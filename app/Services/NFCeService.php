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
use NFePHP\DA\Common\DaEvento;
use App\Models\Nfe;
use NFePHP\DA\Cce;
use NFePHP\NFe\Events;
use NFePHP\NFe\Evento;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use NFePHP\DA\NFe\DanfeNFCe;
use App\Services\RegraTributariaService;
use stdClass;

class NFCeService
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
        $this->tools->model('65');
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

    private function tratarRespostaSefaz(\stdClass $std): array
{
    // Pega o código e o motivo da resposta padronizada
    $codigo = $std->cStat ?? null;
    $motivo = $std->xMotivo ?? 'Motivo não especificado.';

    // Lista de códigos que consideramos SUCESSO
    $codigosSucesso = [
        '100', // Autorizado o uso da NF-e
        '150', // Autorizado o uso da NF-e, autorização fora de prazo
        '135', // Evento registrado e vinculado a NF-e (Sucesso para Cancelamento, CC-e, etc)
        '128', // Lote de Evento Processado (Sucesso para Cancelamento, CC-e, etc)
    ];

    // Lista de códigos que indicam que o lote ainda está sendo processado
    $codigosEmProcessamento = [
        '103', // Lote recebido com sucesso
        '105', // Lote em processamento
    ];

    // Verifica se o código é de sucesso
    if (in_array($codigo, $codigosSucesso)) {
        return ['status' => 'sucesso', 'mensagem' => $motivo];
    }

    // Verifica se o código é de processamento (para consultas futuras)
    if (in_array($codigo, $codigosEmProcessamento)) {
        return ['status' => 'processando', 'mensagem' => "[{$codigo}] {$motivo}"];
    }

    // Se não for nenhum dos acima, consideramos como erro
    return ['status' => 'erro', 'mensagem' => "[{$codigo}] {$motivo}"];
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
            $this->buildTransport($venda);
            $this->buildPayments($venda);
    
            // A validação ocorre aqui, dentro do getXML()
            $xml = $this->nfe->getXML();
    
            // Verificação extra, embora o getXML() já possa lançar uma exceção
            $errors = $this->nfe->getErrors();
            if (count($errors) > 0) {
                throw new \Exception("Erros de validação do XML:\n- " . implode("\n- ", $errors));
            }
    
            $chave = $this->nfe->getChave();
            $xmlAssinado = $this->tools->signNFe($xml);
            
            $protocolo = $this->tools->sefazEnviaLote([$xmlAssinado], $nfeRecord->id, 1);
            
            $stProt = new Standardize($protocolo);
            $stdProt = $stProt->toStd();
            
            $cStat = $stdProt->protNFe->infProt->cStat ?? $stdProt->cStat ?? null;
            $xMotivo = $stdProt->protNFe->infProt->xMotivo ?? $stdProt->xMotivo ?? 'Motivo da rejeição não especificado.';
            
            if (in_array($cStat, ['100', '150'])) {
                $this->handleSuccess($protocolo, $xmlAssinado, $nfeRecord, $chave);
                Venda::whereIn('id', $vendaIdsOriginais)->update(['nfe_chave_acesso' => $chave]);
                DB::commit();
                return ['success' => true, 'message' => "NF-e #{$nfeRecord->numero_nfe} autorizada!", 'chave' => $chave];
            } else {
                throw new \Exception("SEFAZ Rejeitou a Nota: [{$cStat}] {$xMotivo}");
            }
    
        // ======================= INÍCIO DA MUDANÇA PRINCIPAL =======================
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Tenta obter os erros detalhados do objeto NFe, se ele existir
            $detailedErrors = $this->nfe->getErrors() ?? [];
            $finalMessage = '';
    
            if (!empty($detailedErrors)) {
                // Se encontrarmos erros detalhados, formatamos uma mensagem clara para o usuário
                $errorList = implode("\n- ", $detailedErrors);
                $finalMessage = "Foram encontrados os seguintes erros de validação na NF-e:\n\n- " . $errorList;
            } else {
                // Se não houver erros detalhados, usamos a mensagem da exceção original (pode ser um erro de certificado, conexão, etc.)
                $finalMessage = "Erro ao emitir NF-e: " . $e->getMessage();
            }
    
            if (isset($nfeRecord)) {
                $nfeRecord->update(['status' => 'erro', 'mensagem_erro' => $finalMessage]);
            }
    
            // Retorna a mensagem final, que agora é muito mais clara
            return ['success' => false, 'message' => $finalMessage];
        }
        // ======================= FIM DA MUDANÇA PRINCIPAL =======================
    }

    public function cartaCorrecao(Nfe $nfe, string $correcao): array
    {
        DB::beginTransaction();
        try {
            $this->bootstrap($nfe->empresa);
    
            $response = $this->tools->sefazCCe(
                $nfe->chave_acesso,
                $correcao,
                $nfe->cce_sequencia_evento
            );
    
            $st = new Standardize($response);
            $std = $st->toStd();
    
            // ======================= A CORREÇÃO ESTÁ AQUI =======================
            // Verificamos se o status é '135' OU '128' para considerarmos sucesso.
            if (in_array($std->cStat, ['135', '128'])) {
            // ===================================================================
                
                // Incrementa a sequência no banco para a próxima CC-e
                $nfe->increment('cce_sequencia_evento');
    
                // Chama a função que salva os arquivos e o registro na tabela 'cces'
                $this->handleCceSuccess($nfe, $response);
    
                DB::commit();
                $message = ($std->cStat == 128) ? 'processada com sucesso (Status SEFAZ: 128)!' : 'emitida com sucesso!';
                return ['success' => true, 'message' => "Carta de Correção {$message}"];
            } else {
                // Se for qualquer outro status, consideramos como erro.
                throw new \Exception("[{$std->cStat}] {$std->xMotivo}");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function handleCceSuccess(Nfe $nfe, string $responseXml)
    {
        try {
            $chave = $nfe->chave_acesso;
            $sequencia = $nfe->cce_sequencia_evento;
            $anoMes = date('Y-m');
    
            $pathXmlCce = "nfe/xml/{$anoMes}/{$chave}-cce-{$sequencia}.xml";
            Storage::disk('private')->put($pathXmlCce, $responseXml);
    
            $xmlAutorizado = Storage::disk('private')->get($nfe->caminho_xml);
    
            // ======================= CÓDIGO PARA A VERSÃO ATUALIZADA =======================
    
            // 1. Instancia o Danfe com o XML da NFe principal
            $danfe = new Danfe($xmlAutorizado);
    
            // 2. ADICIONA o XML do evento (a resposta da SEFAZ) ao objeto Danfe
            $danfe->addEvento($responseXml);
    
            // 3. Renderiza o PDF, que agora será o do evento (DACCE)
            $pdf = $danfe->render();
    
            // =============================================================================
    
            $pathPdfCce = "nfe/danfe/{$anoMes}/{$chave}-cce-{$sequencia}.pdf";
            Storage::disk('private')->put($pathPdfCce, $pdf);
    
            \App\Models\Cce::create([
                'nfe_id' => $nfe->id,
                'sequencia_evento' => $sequencia,
                'caminho_xml' => $pathXmlCce,
                'caminho_pdf' => $pathPdfCce,
            ]);
    
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $e;
        }
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
        
        $danfe = new DanfeNFCe($xmlAutorizado);
        $pdf = $danfe->render(); // Isso vai gerar o PDF do recibo
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
        // 1. Cria a tag principal <infNFe> com a versão
        $std_infNFe = new \stdClass();
        $std_infNFe->versao = '4.00';
        $this->nfe->taginfNFe($std_infNFe);
    
        // 2. Cria um NOVO objeto para a tag <ide> com todos os seus dados
        $std_ide = new \stdClass();
        $std_ide->cUF = $empresa->codigo_uf;
        $std_ide->cNF = rand(10000000, 99999999);
        
        // Dados específicos e corretos para NFC-e
        $std_ide->natOp = 'VENDA AO CONSUMIDOR';
        $std_ide->mod = 65;
        $std_ide->serie = $serie;
        $std_ide->nNF = $numero;
        $std_ide->dhEmi = date("Y-m-d\TH:i:sP");
        $std_ide->tpNF = 1; // Tipo de Operação (1 - Saída)
        
        // ================== A CORREÇÃO PRINCIPAL ESTÁ AQUI ==================
        // Para NFC-e, idDest é SEMPRE 1 (Operação Interna / Venda presencial)
        $std_ide->idDest = 1; 
        // ====================================================================
    
        $std_ide->cMunFG = $empresa->codigo_municipio;
        $std_ide->tpImp = 4;  // Impressão DANFE NFC-e
        $std_ide->tpEmis = 1; // Emissão Normal
        $std_ide->tpAmb = $this->configArray['tpAmb'];
        $std_ide->finNFe = 1; // Finalidade (1 - Normal)
    
        // ================== GARANTINDO OUTROS CAMPOS DA NFC-e ===============
        $std_ide->indFinal = 1; // Consumidor Final (SEMPRE 1 para NFC-e)
        $std_ide->indPres = 1;  // Indicador de Presença (1 - Presencial)
        // ====================================================================
        
        $std_ide->procEmi = 0;
        $std_ide->verProc = 'Sistema ERP 1.0';
    
        // 3. Adiciona a tag <ide> como filha da <infNFe> que já foi criada
        $this->nfe->tagide($std_ide);
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
        // A função agora só executa alguma ação se houver um cliente na venda.
        if ($venda->cliente) {
            $cliente = $venda->cliente;
            $cpfCnpj = preg_replace('/[^0-9]/', '', $cliente->cpf_cnpj);
    
            $std = new \stdClass();
            
            // Adiciona o CPF ou CNPJ PRIMEIRO
            if (strlen($cpfCnpj) == 14) {
                $std->CNPJ = $cpfCnpj;
            } else {
                $std->CPF = $cpfCnpj;
            }
    
            // DEPOIS, adiciona o nome e outros dados
            $std->xNome = $cliente->nome;
            $std->indIEDest = $cliente->ie ? '1' : '9'; // Para NFC-e, se tiver IE é 1, senão é 9
            if (!empty($cliente->ie)) {
                $std->IE = preg_replace('/[^0-9]/', '', $cliente->ie);
            }
    
            $this->nfe->tagdest($std);
    
            // Adiciona o endereço do cliente
            $stdEnd = new \stdClass();
            $stdEnd->xLgr = $cliente->logradouro;
            $stdEnd->nro = $cliente->numero;
            $stdEnd->xBairro = $cliente->bairro;
            $stdEnd->cMun = $cliente->codigo_municipio;
            $stdEnd->xMun = $cliente->cidade;
            $stdEnd->UF = $cliente->estado;
            $stdEnd->CEP = preg_replace('/[^0-9]/', '', $cliente->cep);
            $stdEnd->cPais = '10_5_8';
            $stdEnd->xPais = 'BRASIL';
            $this->nfe->tagenderDest($stdEnd);
        }
        // Se não houver $venda->cliente (nosso caso no PDV), a função não faz nada,
        // o que é o comportamento correto para "Consumidor não identificado".
    }
    
    private function buildProducts(Venda $venda)
{
    $empresa = $venda->empresa;
    // Define o estado de destino: ou do cliente, ou da própria empresa
    $ufDestino = $venda->cliente->estado ?? $empresa->uf;

    $regraTributariaService = new RegraTributariaService();

    foreach ($venda->items as $i => $item) {
        $produto = $item->produto;
        $cfop = $item->cfop ?? '5102'; // Usamos 5102 como um padrão comum para PDV

        if (empty($cfop)) {
            throw new \Exception("O produto '{$produto->nome}' está sem CFOP definido.");
        }
        
        // CORREÇÃO: Passa a UF de destino correta para o serviço de regras
        $regra = $regraTributariaService->findRule($cfop, $empresa, $ufDestino);

        if (!$regra) {
            throw new \Exception("Nenhuma regra tributária encontrada para o produto '{$produto->nome}' (CFOP {$cfop}, Destino: {$ufDestino}). Verifique as configurações em Admin > Regras Tributárias.");
        }
        
        $impostos = $regraTributariaService->aplicarRegra($regra, $item);

        // --- Montagem do grupo <prod> ---
        $std = new \stdClass();
        $std->item = $i + 1;
        $std->cProd = $produto->id;
        $std->cEAN = $produto->codigo_barras ?: 'SEM GTIN';
        $std->xProd = $produto->nome;
        $std->NCM = $produto->dadosFiscais->ncm ?? null; // Adicionado ?? null para segurança
        $std->CFOP = $cfop; 
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

        // --- Montagem dos impostos (continua igual) ---
        $stdImposto = new \stdClass();
        $stdImposto->item = $i + 1;
        $stdImposto->vTotTrib = 0.00; // Valor aproximado de tributos (opcional)
        $this->nfe->tagimposto($stdImposto);

        // ICMS
        $std_icms = $impostos->ICMS;
        $std_icms->item = $i + 1;
        if ($empresa->crt == 1) { // Simples Nacional
            $this->nfe->tagICMSSN($std_icms);
        } else { // Regime Normal
            $metodoIcms = 'tagICMS' . $std_icms->CST;
             if (method_exists($this->nfe, $metodoIcms)) {
                $this->nfe->{$metodoIcms}($std_icms);
            } else {
                $this->nfe->tagICMS00($std_icms); // Fallback
            }
        }

        // PIS
        $std_pis = $impostos->PIS;
        $std_pis->item = $i + 1;
        $this->nfe->tagPIS($std_pis);

        // COFINS
        $std_cofins = $impostos->COFINS;
        $std_cofins->item = $i + 1;
        $this->nfe->tagCOFINS($std_cofins);
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

    private function buildTransport(Venda $venda)
    {
        $std = new stdClass();
        $std->modFrete = $venda->frete_modalidade ?? 9; // <-- CORRIGIDO
        $this->nfe->tagtransp($std);
    
        // Futuramente: se houver frete, você precisará adicionar os dados da transportadora e volumes aqui.
    }

    public function cancelar(Nfe $nfe, string $justificativa): array
    {
        try {
            $this->bootstrap($nfe->empresa);
    
            $chave = $nfe->chave_acesso;
            $protocolo = $nfe->protocolo_autorizacao;
            $sequenciaEvento = 1;
            $dhEvento = new \DateTime("now");
    
            $response = $this->tools->sefazCancela(
                $chave,
                $justificativa,
                $protocolo,
                $dhEvento,
                $sequenciaEvento
            );
    
            // ====================== INÍCIO DA CORREÇÃO ======================
    
            // 1. Verificamos se houve uma resposta válida da SEFAZ
            if (empty($response)) {
                throw new \Exception("A SEFAZ não retornou uma resposta. Verifique a conexão, o certificado e o status do webservice.");
            }
    
            $st = new \NFePHP\NFe\Common\Standardize($response);
            $std = $st->toStd();
    
            // 2. A resposta do evento (cancelamento, cce) geralmente fica aninhada.
            //    Procuramos pelo objeto 'infEvento' que contém o 'cStat' e 'xMotivo'.
            $respostaEvento = $std->retEvento->infEvento ?? $std;
            
            // 3. Passamos o objeto correto (que contém o status) para o nosso tratador
            $resultadoSefaz = $this->tratarRespostaSefaz($respostaEvento);
            
            // ======================= FIM DA CORREÇÃO ========================
    
            if ($resultadoSefaz['status'] === 'sucesso') {
                $this->handleCancelSuccess($nfe, $justificativa, $response);
                return ['success' => true, 'message' => 'NF-e cancelada com sucesso!'];
            } else {
                // A mensagem de erro agora vem diretamente do nosso tratador
                throw new \Exception($resultadoSefaz['mensagem']);
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    
    private function buildPayments(Venda $venda)
    {
        $this->nfe->tagpag(new \stdClass());
        
        if ($venda->pagamentos->isEmpty()) {
            $det = new \stdClass();
            $det->tPag = '01'; // Dinheiro
            $det->vPag = number_format($venda->total, 2, '.', '');
            $this->nfe->tagdetPag($det);
        } 
        else {
            foreach ($venda->pagamentos as $pagamento) {
                $codigoPagamento = $pagamento->formaPagamento->codigo_sefaz;
    
                $det = new \stdClass();
                $det->tPag = $codigoPagamento;
                $det->vPag = number_format($pagamento->valor, 2, '.', '');
    
                // Verifica se é Cartão de Crédito (03) ou Débito (04)
                if (in_array($codigoPagamento, ['03', '04'])) {
                    // 1. Adiciona o tipo de integração ao detalhe do pagamento
                    $det->tpIntegra = 1;
    
                    // 2. Cria um objeto SEPARADO para os dados do cartão
                    $card = new \stdClass();
                    $card->tpIntegra = 1;
                    $card->CNPJ = '12345678000199'; // PREENCHA COM O CNPJ REAL DA OPERADORA
                    $card->tBand = '99'; // 99 = Outros
                    $card->cAut = 'ABC123456'; // Código de autorização
    
                    // 3. ANEXA o objeto do cartão como uma propriedade do objeto de pagamento
                    $det->card = $card;
                }
    
                // 4. Envia o objeto de pagamento COMPLETO (com ou sem a propriedade 'card') para a função
                $this->nfe->tagdetPag($det);
            }
        }
    }
}