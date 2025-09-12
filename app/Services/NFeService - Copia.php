<?php

namespace App\Services;

// ===== NAMESPACES CORRETOS E FINAIS PARA A BIBLIOTECA nfephp/nfe v4 =====
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Config;
use NFePHP\NFe\Danfe;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Common\Standardize;
// =========================================================================

use App\Models\Empresa;
use App\Models\Nfe;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class NFeService
{
    private $tools;
    private $config;
    private $nfe;
    private $configArray;

    public function __construct()
    {
        $this->nfe = new Make();
    }

    private function bootstrap(Empresa $empresa)
    {
        if (empty($empresa->certificado_a1_path) || empty($empresa->certificado_a1_password)) {
            throw new \Exception("Certificado digital A1 ou senha não cadastrados para a empresa.");
        }
        $certificatePath = Storage::disk('private')->path($empresa->certificado_a1_path);
        if (!file_exists($certificatePath)) {
            throw new \Exception("Arquivo do certificado digital não encontrado no sistema.");
        }

        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) $empresa->ambiente_nfe,
            'razaosocial' => $empresa->razao_social,
            'siglaUF'     => $empresa->uf,
            'cnpj'        => preg_replace('/[^0-9]/', '', $empresa->cnpj),
            'schemes'     => 'PL_009_v4',
            'versao'      => '4.00',
            'tokenIBPT'   => '',
            'CSC'         => $empresa->csc_nfe,
            'CSCid'       => $empresa->csc_id_nfe,
        ];

        $this->configArray = $config;

        $configJson = json_encode($config);
        $this->config = new Config($configJson);
        
        try {
            $certificate = Certificate::readPfx(file_get_contents($certificatePath), $empresa->certificado_a1_password);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'mac verify failure')) {
                throw new \Exception('A senha do certificado digital está incorreta.');
            }
            throw new \Exception('Não foi possível ler o certificado. Erro: ' . $e->getMessage());
        }

        $this->tools = new Tools($configJson, $certificate);
        $this->tools->model('55');
    }
    
    public function emitirDeVendas(array $vendaIds): array
    {
        // ======================= A CORREÇÃO ESTÁ AQUI =======================
        // Trocamos 'pagamentos' por 'pagamentos.forma' para carregar o relacionamento aninhado
        $vendas = Venda::with('cliente', 'items.produto.dadosFiscais', 'pagamentos.forma', 'empresa')->whereIn('id', $vendaIds)->get();
        // ====================================================================
    
        if ($vendas->isEmpty()) return ['success' => false, 'message' => 'Nenhuma venda encontrada.'];
    
        $empresa = $vendas->first()->empresa;
        $cliente = $vendas->first()->cliente;
        $vendaConsolidada = new Venda();
        $vendaConsolidada->setRelation('empresa', $empresa);
        $vendaConsolidada->setRelation('cliente', $cliente);
        $itensAgrupados = $vendas->pluck('items')->flatten()->groupBy('produto_id')->map(function ($items) {
            $primeiroItem = clone $items->first();
            $primeiroItem->quantidade = $items->sum('quantidade');
            $primeiroItem->subtotal_item = $items->sum('subtotal_item');
            return $primeiroItem;
        });
        $vendaConsolidada->setRelation('items', $itensAgrupados->values());
        $vendaConsolidada->setRelation('pagamentos', $vendas->pluck('pagamentos')->flatten());
        $vendaConsolidada->total = $vendas->sum('total');
        return $this->emitir($vendaConsolidada, $vendaIds);
    }
    
    public function emitir(Venda $venda, array $vendaIdsOriginais = null)
    {
        DB::beginTransaction();
        try {
            $empresa = $venda->empresa;
            if (is_null($vendaIdsOriginais)) $vendaIdsOriginais = [$venda->id];
            
            $this->bootstrap($empresa);
    
            // ===== INÍCIO DA CORREÇÃO =====
            // 1. Calcula o número e a série ANTES de usar as variáveis.
            $serie = 1;
            $numero = $this->getNextNFeNumber($empresa, $serie);
            // ===== FIM DA CORREÇÃO =====
    
            $nfeRecord = Nfe::create([
                'empresa_id' => $empresa->id, 
                'venda_id' => $vendaIdsOriginais[0],
                'status' => 'processando', 
                'ambiente' => $this->configArray['tpAmb'],
                'serie' => $serie,      // Agora a variável $serie existe
                'numero_nfe' => $numero, // Agora a variável $numero existe
            ]);
    
            // A chamada para buildHeader agora funciona, pois as variáveis foram definidas.
            $this->buildHeader($empresa, $venda, $numero, $serie);
            
            $this->buildEmitter($empresa);
            $this->buildRecipient($venda);
            $this->buildProducts($venda);
            $this->buildTotals();
            $this->buildTransport();
            $this->buildPayments($venda);
    
            $xml = $this->nfe->getXML();
            $chave = $this->nfe->getChave();
            
            $xmlAssinado = $this->tools->signNFe($xml);
            $response = $this->tools->sefazEnviaLote([$xmlAssinado], $nfeRecord->id);
            $st = new Standardize($response);
            $std = $st->toStd();
            
            if ($std->cStat != '103' && $std->cStat != '104') {
                throw new \Exception("SEFAZ Rejeitou o Lote: [{$std->cStat}] {$std->xMotivo}");
            }
            
            $recibo = $std->infRec->nRec;
            sleep(3);
            $protocol = $this->tools->sefazConsultaRecibo($recibo);
            
            $stProt = new Standardize($protocol);
            $stdProt = $stProt->toStd();
    
            if (in_array($stdProt->cStat, ['100', '150'])) {
                $this->handleSuccess($stdProt, $xmlAssinado, $nfeRecord, $chave);
                Venda::whereIn('id', $vendaIdsOriginais)->update(['nfe_chave_acesso' => $chave]);
                DB::commit();
                return ['success' => true, 'message' => "NF-e #{$nfeRecord->numero_nfe} autorizada!", 'chave' => $chave];
            } else {
                throw new \Exception("SEFAZ Rejeitou a Nota: [{$stdProt->cStat}] {$stdProt->xMotivo}");
            }
    
        } catch (\Exception $e) {
            $errors = $this->nfe->getErrors();
            $detailedMessage = $e->getMessage();
    
            if (count($errors) > 0) {
                $detailedMessage .= "\n\nDetalhes dos Erros de Validação:\n- " . implode("\n- ", $errors);
            }
            
            DB::rollBack();
            if (isset($nfeRecord)) {
                $nfeRecord->update(['status' => 'erro', 'mensagem_erro' => $detailedMessage]);
            }
            return ['success' => false, 'message' => "Erro ao emitir NF-e: " . $detailedMessage];
        }
    }

    private function handleSuccess($protocolo, $xmlAssinado, Nfe $nfeRecord, $chave)
    {
        $xmlAutorizado = $this->tools->addProtocol($xmlAssinado, $protocolo);
        $anoMes = date('Y-m');
        $pathXml = "nfe/xml/{$anoMes}/{$chave}.xml";
        Storage::disk('private')->put($pathXml, $xmlAutorizado);
        
        $danfe = new Danfe($xmlAutorizado);
        $pdf = $danfe->render();
        $pathDanfe = "nfe/danfe/{$anoMes}/{$chave}.pdf";
        Storage::disk('private')->put($pathDanfe, $pdf);
        
        $nfeRecord->update([
            'status' => 'autorizada',
            'chave_acesso' => $chave,
            'caminho_xml' => $pathXml,
            'caminho_danfe' => $pathDanfe,
            'numero_nfe' => $this->nfe->getNumero(),
            'serie' => $this->nfe->getSerie(),
        ]);
    }

    private function getNextNFeNumber(Empresa $empresa, int $serie): int
    {
        $max = Nfe::where('empresa_id', $empresa->id)->where('serie', $serie)->max('numero_nfe');
        return $max + 1;
    }

    private function buildHeader(Empresa $empresa, Venda $venda, int $numero, int $serie)
    {
        $std = new stdClass();
        $std->versao = '4.00';
        $this->nfe->taginfNFe($std);

        $std = new stdClass();
        $std->cUF = $empresa->codigo_uf;
        $std->cNF = rand(10000000, 99999999);
        $std->natOp = 'VENDA DE MERCADORIAS';
        $std->mod = 55;
        $std->serie = $serie; // Usa o parâmetro
        $std->nNF = $numero; // Usa o parâmetro
        $std->dhEmi = date("Y-m-d\TH:i:sP");
        $std->tpNF = 1;
        $std->idDest = ($empresa->uf == $venda->cliente->estado) ? 1 : 2;
        $std->cMunFG = $empresa->codigo_municipio;
        $std->tpImp = 1; 
        $std->tpEmis = 1; 
        $std->tpAmb = $this->configArray['tpAmb'];
        $std->finNFe = 1; 
        $std->indFinal = 1; 
        $std->indPres = 1;
        $std->procEmi = 0; 
        $std->verProc = 'Sistema ERP 1.0';
        $this->nfe->tagide($std);
    }
    
    private function buildEmitter(Empresa $empresa)
    {
        $std = new stdClass();
        $std->xNome = $empresa->razao_social;
        $std->xFant = $empresa->nome_fantasia;
        $std->IE = preg_replace('/[^0-9]/', '', $empresa->ie);
        $std->CRT = $empresa->crt;
        $std->CNPJ = preg_replace('/[^0-9]/', '', $empresa->cnpj);
        $this->nfe->tagemit($std);

        $std = new stdClass();
        $std->xLgr = $empresa->logradouro;
        $std->nro = $empresa->numero;
        $std->xBairro = $empresa->bairro;
        $std->cMun = $empresa->codigo_municipio;
        $std->xMun = $empresa->municipio;
        $std->UF = $empresa->uf;
        $std->CEP = preg_replace('/[^0-9]/', '', $empresa->cep);
        $std->cPais = '1058';
        $std->xPais = 'BRASIL';
        $std->fone = preg_replace('/[^0-9]/', '', $empresa->telefone);
        $this->nfe->tagenderEmit($std);
    }

    private function buildRecipient(Venda $venda)
    {
        $cliente = $venda->cliente;
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cliente->cpf_cnpj);

        $std = new stdClass();
        $std->xNome = $cliente->nome;

        if (strlen($cpfCnpj) == 14) {
            $std->indIEDest = $cliente->ie ? '1' : '2'; // 1-Contribuinte, 2-Isento
            $std->IE = preg_replace('/[^0-9]/', '', $cliente->ie ?? '');
            $std->CNPJ = $cpfCnpj;
        } else {
            $std->indIEDest = '9'; // 9-Não Contribuinte
            $std->CPF = $cpfCnpj;
        }
        $std->email = $cliente->email;
        $this->nfe->tagdest($std);

        $std = new stdClass();
        $std->xLgr = $cliente->logradouro;
        $std->nro = $cliente->numero;
        $std->xBairro = $cliente->bairro;
        $std->cMun = $cliente->codigo_municipio;
        $std->xMun = $cliente->cidade;
        $std->UF = $cliente->estado;
        $std->CEP = preg_replace('/[^0-9]/', '', $cliente->cep);
        $std->cPais = '1058';
        $std->xPais = 'BRASIL';
        $this->nfe->tagenderDest($std);
    }
    
    private function buildProducts(Venda $venda)
    {
        $empresa = $venda->empresa;
        foreach ($venda->items as $i => $item) {
            $produto = $item->produto;
            $dadosFiscais = $produto->dadosFiscais;
            if (!$dadosFiscais) {
                throw new \Exception("Dados fiscais não encontrados para o produto: {$produto->nome}");
            }
    
            // --- Montagem do grupo <prod> (Sem alterações) ---
            $std = new stdClass();
            $std->item = $i + 1;
            $std->cProd = $produto->id;
            $std->cEAN = $produto->codigo_barras ?: 'SEM GTIN';
            $std->xProd = $produto->nome;
            $std->NCM = $dadosFiscais->ncm;
            $std->CFOP = $dadosFiscais->cfop;
            $std->uCom = $produto->unidade;
            $std->qCom = $item->quantidade;
            $std->vUnCom = $item->preco_unitario;
            $std->vProd = $item->subtotal_item;
            $std->cEANTrib = $produto->codigo_barras ?: 'SEM GTIN';
            $std->uTrib = $produto->unidade;
            $std->qTrib = $item->quantidade;
            $std->vUnTrib = $item->preco_unitario;
            $std->indTot = 1;
            $this->nfe->tagprod($std);
    
            // --- Montagem do grupo <imposto> (Sem alterações) ---
            $std = new stdClass();
            $std->item = $i + 1;
            $std->vTotTrib = 0.00;
            $this->nfe->tagimposto($std);
    
            // --- Montagem do ICMS (Sem alterações) ---
            if ($empresa->crt == 1) { // Simples Nacional
                $std = new stdClass();
                $std->item = $i + 1;
                $std->orig = $dadosFiscais->origem;
                $std->CSOSN = $dadosFiscais->csosn;
                $this->nfe->tagICMSSN($std);
            } else { // Regime Normal
                $std = new stdClass();
                $std->item = $i + 1;
                $std->orig = $dadosFiscais->origem;
                $std->CST = $dadosFiscais->icms_cst;
                $std->modBC = 0; $std->vBC = 0; $std->pICMS = 0; $std->vICMS = 0;
                $this->nfe->tagICMS($std);
            }
            
            // ======================= INÍCIO DA CORREÇÃO FINAL PIS/COFINS =======================
            // Para o PIS
            $stdPIS = new stdClass();
            $stdPIS->item = $i + 1; // Item
    
            $stdPISNT = new stdClass();
            $stdPISNT->CST = $dadosFiscais->pis_cst;
            
            $stdPIS->PISNT = $stdPISNT; // Aninha o PISNT dentro do PIS
            $this->nfe->tagPIS($stdPIS); // Adiciona o grupo PIS completo
    
            // Para o COFINS
            $stdCOFINS = new stdClass();
            $stdCOFINS->item = $i + 1; // Item
    
            $stdCOFINSNT = new stdClass();
            $stdCOFINSNT->CST = $dadosFiscais->cofins_cst;
    
            $stdCOFINS->COFINSNT = $stdCOFINSNT; // Aninha o COFINSNT dentro do COFINS
            $this->nfe->tagCOFINS($stdCOFINS); // Adiciona o grupo COFINS completo
            // ======================= FIM DA CORREÇÃO FINAL PIS/COFINS =======================
        }
    }
    private function buildTotals()
    {
        $this->nfe->tagICMSTot(new stdClass());
    }

    private function buildTransport()
    {
        $std = new stdClass();
        $std->modFrete = 9; // 9-Sem Frete
        $this->nfe->tagtransp($std);
    }

    
    



    private function buildPayments(Venda $venda)
    {
        // 1. Cria o grupo de pagamento <pag> PRIMEIRO.
        $this->nfe->tagpag(new stdClass());
    
        // 2. Agora, adiciona os detalhes de cada pagamento <detPag> DENTRO do grupo.
        if ($venda->pagamentos->isEmpty()) {
            $det = new stdClass();
            $det->tPag = '99'; // 99-Outros
            $det->vPag = number_format($venda->total, 2, '.', '');
            $this->nfe->tagdetPag($det);
        } else {
            foreach ($venda->pagamentos as $pagamento) {
                $det = new stdClass();
    
                // ======================= A CORREÇÃO FINAL ESTÁ AQUI =======================
                // Acessamos o relacionamento 'forma' para pegar o 'codigo_sefaz'
                $det->tPag = $pagamento->forma->codigo_sefaz;
                // ========================================================================
                
                $det->vPag = number_format($pagamento->valor, 2, '.', '');
                if (in_array($det->tPag, ['03', '04'])) { // Cartão de Crédito/Débito
                    $det->tpIntegra = 2; // 2-Não integrado com o sistema de automação
                }
                $this->nfe->tagdetPag($det);
            }
        }
    }
}