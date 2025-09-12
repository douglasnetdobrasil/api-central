<?php

namespace App\Services;

// Namespaces corretos e validados para a biblioteca nfephp/nfe v4
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Config;
use NFePHP\DA\NFe\Danfe;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Common\Standardize;
use App\Models\Empresa;
use NFePHP\NFe\Complements;
use App\Models\Nfe;
use NFePHP\NFe\Events;
use NFePHP\DA\CCe;
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
        $vendas = Venda::with('cliente', 'items.produto.dadosFiscais', 'pagamentos.forma', 'empresa')->whereIn('id', $vendaIds)->get();
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

            $serie = 1;
            $numero = $this->getNextNFeNumber($empresa, $serie);

            $nfeRecord = Nfe::create([
                'empresa_id' => $empresa->id, 'venda_id' => $vendaIdsOriginais[0],
                'status' => 'processando', 'ambiente' => $this->configArray['tpAmb'],
                'serie' => $serie, 'numero_nfe' => $numero,
            ]);

            $this->buildHeader($empresa, $venda, $numero, $serie);
            $this->buildEmitter($empresa);
            $this->buildRecipient($venda);
            $this->buildProducts($venda);
            $this->buildTotals();
            $this->buildTransport();
            $this->buildPayments($venda);

            $xml = $this->nfe->getXML();
            $errors = $this->nfe->getErrors();
            if (count($errors) > 0) {
                throw new \Exception("Erros de validação do XML:\n- " . implode("\n- ", $errors));
            }

            $chave = $this->nfe->getChave();
            $xmlAssinado = $this->tools->signNFe($xml);
            // 1. FORÇA O ENVIO SÍNCRONO: Adiciona o parâmetro '1' ao final da chamada.
            // Isso é obrigatório para lotes com apenas uma NF-e.
            $protocolo = $this->tools->sefazEnviaLote([$xmlAssinado], $nfeRecord->id, 1);
            
            // 2. TRATA A RESPOSTA DIRETA: A variável $protocolo já contém o XML com o resultado
            // final do processamento. A consulta de recibo não é mais necessária.
            $stProt = new Standardize($protocolo);
            $stdProt = $stProt->toStd();
            
            // Tenta obter o status de dentro da tag <protNFe>. Se não existir, usa o status geral.
            $cStat = $stdProt->protNFe->infProt->cStat ?? $stdProt->cStat ?? null;
            $xMotivo = $stdProt->protNFe->infProt->xMotivo ?? $stdProt->xMotivo ?? 'Motivo da rejeição não especificado.';
            
            // Status '100' (Autorizado) ou '150' (Autorizado fora de prazo) indicam sucesso.
            if (in_array($cStat, ['100', '150'])) {
                // A nota foi autorizada com sucesso.
                // O método handleSuccess espera o XML do protocolo como primeiro parâmetro.
                $this->handleSuccess($protocolo, $xmlAssinado, $nfeRecord, $chave);
                Venda::whereIn('id', $vendaIdsOriginais)->update(['nfe_chave_acesso' => $chave]);
                DB::commit();
                return ['success' => true, 'message' => "NF-e #{$nfeRecord->numero_nfe} autorizada!", 'chave' => $chave];
            } else {
                // A nota foi processada, mas rejeitada pela SEFAZ.
                throw new \Exception("SEFAZ Rejeitou a Nota: [{$cStat}] {$xMotivo}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($nfeRecord)) {
                $nfeRecord->update(['status' => 'erro', 'mensagem_erro' => $e->getMessage()]);
            }
            return ['success' => false, 'message' => "Erro ao emitir NF-e: " . $e->getMessage()];
        }
    }

    public function cartaCorrecao(Nfe $nfe, string $correcao): array
{
    DB::beginTransaction();
    try {
        $this->bootstrap($nfe->empresa);

        // Usa o método sefazCCe da classe Tools
        $response = $this->tools->sefazCCe(
            $nfe->chave_acesso,
            $correcao,
            $nfe->cce_sequencia_evento // Número da sequência (1, 2, 3...)
        );

        $st = new Standardize($response);
        $std = $st->toStd();

        // 135 = Evento registrado e vinculado a NF-e (sucesso)
        if ($std->cStat == '135') {
            // Incrementa a sequência no banco para a próxima CC-e
            $nfe->increment('cce_sequencia_evento');

            // Lógica para salvar os arquivos da CC-e (opcional, mas recomendado)
            $this->handleCceSuccess($nfe, $response);

            DB::commit();
            return ['success' => true, 'message' => 'Carta de Correção emitida com sucesso!'];
        } else {
            throw new \Exception("[{$std->cStat}] {$std->xMotivo}");
        }
    } catch (\Exception $e) {
        DB::rollBack();
        // Também tratamos o caso de sucesso "128" que vem como exceção
        if (str_contains($e->getMessage(), '[128] Lote de Evento Processado')) {
            $nfe->increment('cce_sequencia_evento');
            DB::commit();
            return ['success' => true, 'message' => 'Carta de Correção processada com sucesso (Status SEFAZ: 128)!'];
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

private function handleCceSuccess(Nfe $nfe, string $responseXml)
{
    $chave = $nfe->chave_acesso;
    // A sequência é o valor ATUAL no banco, pois o increment já foi feito.
    $sequencia = $nfe->cce_sequencia_evento; 
    $anoMes = date('Y-m');

    $pathXmlCce = "nfe/xml/{$anoMes}/{$chave}-cce-{$sequencia}.xml";
    Storage::disk('private')->put($pathXmlCce, $responseXml);

    $xmlAutorizado = Storage::disk('private')->get($nfe->caminho_xml);
    $dacce = new CCe($xmlAutorizado, $responseXml);
    $pdf = $dacce->render();
    $pathPdfCce = "nfe/danfe/{$anoMes}/{$chave}-cce-{$sequencia}.pdf";
    Storage::disk('private')->put($pathPdfCce, $pdf);

    // Salva os caminhos na nova tabela
    Cce::create([
        'nfe_id' => $nfe->id,
        'sequencia_evento' => $sequencia,
        'caminho_xml' => $pathXmlCce,
        'caminho_pdf' => $pathPdfCce,
    ]);
}

    private function handleSuccess($protocolo, $xmlAssinado, Nfe $nfeRecord, $chave)
    {
        // Extrai o número do protocolo do XML de resposta
        $stProt = new Standardize($protocolo);
        $stdProt = $stProt->toStd();
        $numeroProtocolo = $stdProt->protNFe->infProt->nProt ?? null;
    
        $xmlAutorizado = Complements::toAuthorize($xmlAssinado, $protocolo);
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
            'protocolo_autorizacao' => $numeroProtocolo, // <-- SALVA O PROTOCOLO
            'caminho_xml' => $pathXml,
            'caminho_danfe' => $pathDanfe,
        ]);
    }

    private function getNextNFeNumber(Empresa $empresa, int $serie): int
    {
        // 1. Busca o número máximo de NFe já emitido E REGISTRADO no seu banco.
        $max = Nfe::where('empresa_id', $empresa->id)->where('serie', $serie)->max('numero_nfe');

        // 2. Se já existe um número máximo ($max > 0), isso significa que seu sistema
        //    já emitiu notas para esta empresa. A sequência deve continuar a partir daí.
        if ($max) {
            return $max + 1;
        }

        // 3. SE NÃO HÁ NOTAS emitidas pelo seu sistema, verificamos se há um número
        //    inicial configurado no cadastro da empresa.
        //    O '?? 1' serve como segurança: se o campo for nulo, ele começa do 1.
        $numeroInicial = $empresa->nfe_proximo_numero ?? 1;

        return $numeroInicial;
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
        $std->serie = $serie;
        $std->nNF = $numero;
        $std->dhEmi = date("Y-m-d\TH:i:sP");
        $std->tpNF = 1;
        $std->idDest = ($empresa->uf == $venda->cliente->estado) ? 1 : 2;
        $std->cMunFG = $empresa->codigo_municipio;
        $std->tpImp = 1; $std->tpEmis = 1; $std->tpAmb = $this->configArray['tpAmb'];
        $std->finNFe = 1; $std->indFinal = 1; $std->indPres = 1;
        $std->procEmi = 0; $std->verProc = 'Sistema ERP 1.0';
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
            $std->indIEDest = $cliente->ie ? '1' : '2';
            $std->IE = preg_replace('/[^0-9]/', '', $cliente->ie ?? '');
            $std->CNPJ = $cpfCnpj;
        } else {
            $std->indIEDest = '9';
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
            if (empty($dadosFiscais->pis_cst) || empty($dadosFiscais->cofins_cst)) {
                throw new \Exception("Os códigos CST de PIS e COFINS são obrigatórios no cadastro fiscal do produto: {$produto->nome}");
            }
    
            // --- Montagem do grupo <prod> (Sem alterações) ---
            $std = new stdClass();
            $std->item = $i + 1;
            $std->cProd = $produto->id; $std->cEAN = $produto->codigo_barras ?: 'SEM GTIN';
            $std->xProd = $produto->nome; $std->NCM = $dadosFiscais->ncm;
            $std->CFOP = $dadosFiscais->cfop; $std->uCom = $produto->unidade;
            $std->qCom = $item->quantidade; $std->vUnCom = $item->preco_unitario;
            $std->vProd = $item->subtotal_item; $std->cEANTrib = $produto->codigo_barras ?: 'SEM GTIN';
            $std->uTrib = $produto->unidade; $std->qTrib = $item->quantidade;
            $std->vUnTrib = $item->preco_unitario; $std->indTot = 1;
            $this->nfe->tagprod($std);
    
            // --- Montagem do grupo <imposto> (Sem alterações) ---
            $std = new stdClass();
            $std->item = $i + 1;
            $this->nfe->tagimposto($std);
    
            // --- Montagem do ICMS (Sem alterações) ---
            if ($empresa->crt == 1) { // Simples Nacional
                $std = new stdClass();
                $std->item = $i + 1; $std->orig = $dadosFiscais->origem;
                $std->CSOSN = $dadosFiscais->csosn;
                $this->nfe->tagICMSSN($std);
            } else { // Regime Normal
                // ... sua lógica para regime normal ...
            }
            
            // ======================= INÍCIO DA CORREÇÃO FINAL PIS/COFINS =======================
            // A forma correta é passar os dados do "conteúdo" diretamente para o método da "caixa".
            
            // Para o PIS
            $std = new stdClass();
            $std->item = $i + 1;
            $std->CST = $dadosFiscais->pis_cst; // A biblioteca usará este CST para criar o sub-bloco correto (PISNT)
            $this->nfe->tagPIS($std); // Chamamos apenas o método principal
    
            // Para o COFINS
            $std = new stdClass();
            $std->item = $i + 1;
            $std->CST = $dadosFiscais->cofins_cst; // A biblioteca usará este CST para criar o sub-bloco correto (COFINSNT)
            $this->nfe->tagCOFINS($std); // Chamamos apenas o método principal
            // ======================= FIM DA CORREÇÃO FINAL PIS/COFINS =======================
        }
    }

    public function cancelar(Nfe $nfe, string $justificativa): array
    {
        DB::beginTransaction();
        try {
            $this->bootstrap($nfe->empresa);
    
            if (empty($nfe->protocolo_autorizacao)) {
                throw new \Exception('O número do protocolo de autorização não foi encontrado para esta NF-e.');
            }
    
            $response = $this->tools->sefazCancela(
                $nfe->chave_acesso,
                $justificativa,
                $nfe->protocolo_autorizacao
            );
    
            $st = new Standardize($response);
            $std = $st->toStd();
    
            // Cenário 1: Sucesso padrão (ex: 135 - Evento Vinculado)
            if ($std->cStat == '135') {
                $this->handleCancelSuccess($nfe, $justificativa, $response);
                DB::commit();
                return ['success' => true, 'message' => 'NF-e cancelada com sucesso!'];
            } else {
                // Outras respostas da SEFAZ que não são exceções, mas também não são sucesso
                throw new \Exception("[{$std->cStat}] {$std->xMotivo}");
            }
    
        } catch (\Exception $e) {
            // ======================= AJUSTE FINAL =======================
            // Cenário 2: Sucesso retornado como exceção (código 128)
            if (str_contains($e->getMessage(), '[128] Lote de Evento Processado')) {
                
                // Se a exceção for o status 128, consideramos como SUCESSO.
                // A única limitação é que não conseguimos o XML de resposta para anexar,
                // mas o mais importante é atualizar o status local.
                $nfe->update([
                    'status' => 'cancelada',
                    'justificativa_cancelamento' => $justificativa,
                ]);
                DB::commit();
                return ['success' => true, 'message' => 'NF-e cancelada com sucesso (Status SEFAZ: 128)!'];
    
            } else {
                // Cenário 3: Erro real (qualquer outra exceção)
                DB::rollBack();
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
    }
    
    /**
     * Método auxiliar para tratar a lógica de sucesso do cancelamento.
     */
    private function handleCancelSuccess(Nfe $nfe, string $justificativa, string $responseXml)
    {
        $nfe->update([
            'status' => 'cancelada',
            'justificativa_cancelamento' => $justificativa,
        ]);
        
        $xmlAutorizado = Storage::disk('private')->get($nfe->caminho_xml);
        $xmlCancelado = Complements::cancelRegister($xmlAutorizado, $responseXml);
        Storage::disk('private')->put($nfe->caminho_xml, $xmlCancelado);
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
        $this->nfe->tagpag(new stdClass());
        if ($venda->pagamentos->isEmpty()) {
            $det = new stdClass();
            $det->tPag = '99';
            $det->vPag = number_format($venda->total, 2, '.', '');
            $this->nfe->tagdetPag($det);
        } else {
            foreach ($venda->pagamentos as $pagamento) {
                $det = new stdClass();
                $det->tPag = $pagamento->forma->codigo_sefaz;
                $det->vPag = number_format($pagamento->valor, 2, '.', '');
                if (in_array($det->tPag, ['03', '04'])) {
                    $det->tpIntegra = 2;
                }
                $this->nfe->tagdetPag($det);
            }
        }
    }
}