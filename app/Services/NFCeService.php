<?php

namespace App\Services;

// Namespaces corretos e validados para a biblioteca nfephp/nfe v4
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Config;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFe\Danfce;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Common\Standardize;
use App\Models\Empresa;
use NFePHP\NFe\Complements;
use NFePHP\DA\Common\DaEvento;
use App\Models\Nfe;
use Illuminate\Support\Facades\Log;
use NFePHP\DA\Cce;
use NFePHP\NFe\Events;
use NFePHP\NFe\Evento;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\RegraTributariaService;
use Illuminate\Support\Facades\URL;
use stdClass;

class NFCeService
{
    private $tools;
    private $config;
    private $nfe;
    private $configArray;

    public function __construct()
    {
       // $this->nfe = new Make();
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

        $logDir = storage_path('logs/nfe');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) $empresa->ambiente_nfe,
            'razaosocial' => $empresa->razao_social,
            'siglaUF'     => $empresa->uf,
            'cnpj'        => preg_replace('/[^0-9]/', '', $empresa->cnpj),
            'schemes'     => 'PL_009_v4',
            'versao'      => '4.00',
            'CSC'         => $empresa->csc_nfe,
            'CSCid'       => $empresa->csc_id_nfe,
            'debugMode'   => true,
            'pathLogs'    => $logDir,
            'forceSoap'   => 1, // <-- ADIÇÃO PARA FORÇAR O USO DO SoapClient DO PHP
        ];

        $this->configArray = $config; 
        $configJson = json_encode($config);
        
        try {
            // CORREÇÃO DO ERRO DE DIGITAÇÃO: de 'certificado_a_password' para 'certificado_a1_password'
            $certificate = Certificate::readPfx(file_get_contents($certificatePath), $empresa->certificado_a1_password);
        } catch (\Exception $e) {
            throw new \Exception('Não foi possível ler o certificado. Senha incorreta ou arquivo corrompido.');
        }

        $this->tools = new Tools($configJson, $certificate);
        $this->tools->model('65');
    }
    
    public function emitirDeVendas(array $vendaIds): array
    {
        // Sua função original - Mantida
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
        // Sua função original - Mantida
        $codigo = $std->cStat ?? null;
        $motivo = $std->xMotivo ?? 'Motivo não especificado.';
        $codigosSucesso = ['100', '150', '135', '128'];
        $codigosEmProcessamento = ['103', '105'];

        if (in_array($codigo, $codigosSucesso)) {
            return ['status' => 'sucesso', 'mensagem' => $motivo];
        }

        if (in_array($codigo, $codigosEmProcessamento)) {
            return ['status' => 'processando', 'mensagem' => "[{$codigo}] {$motivo}"];
        }
        
        return ['status' => 'erro', 'mensagem' => "[{$codigo}] {$motivo}"];
    }
    
    // ==================================================================
    // FUNÇÃO 'EMITIR' ATUALIZADA
    // - Adiciona retentativa para erro de duplicidade (539)
    // - Corrige a chamada para ser síncrona para NFCe (erro 452)
    // ==================================================================
    public function emitir(Venda $venda)
    {
        try {
            // Tenta a emissão normal (Síncrona)
            return $this->tentarEmissaoNormal($venda);
        } catch (\Exception $e) {
            // Verifica se a falha foi de comunicação para entrar em contingência
            $isConnectionError = str_contains($e->getMessage(), 'SOAP') || // <- Mais genérico
            str_contains($e->getMessage(), 'cURL error') ||
            str_contains($e->getMessage(), 'Could not resolve host') ||
            str_contains($e->getMessage(), 'comunicação') || // <- Adicionado a partir do seu teste
            str_contains($e->getMessage(), 'timeout');
            if ($isConnectionError) {
                Log::warning('Falha de comunicação com a SEFAZ. Entrando em modo de contingência. Erro: ' . $e->getMessage());
                // Se a emissão normal falhou por conexão, tenta emitir em contingência
                try {
                    return $this->emitirEmContingencia($venda, 'Falha de comunicacao com a SEFAZ.');
                } catch (\Exception $contingencyException) {
                    // Se até a contingência falhar, retorna o erro da contingência
                    return ['success' => false, 'message' => 'Falha crítica ao tentar emitir em contingência: ' . $contingencyException->getMessage()];
                }
            }

            // Se for outro tipo de erro (ex: validação de XML), retorna o erro original
            return ['success' => false, 'message' => 'Erro de emissão: ' . $e->getMessage()];
        }
    }


    private function tentarEmissaoNormal(Venda $venda)
    {
        // Esta função usa o loop de retentativa que você já tinha.
        for ($i = 0; $i < 5; $i++) {
            $this->nfe = new Make();
            DB::beginTransaction();
            try {
                $this->bootstrap($venda->empresa);

                $serie = 1; 
                $modelo = '65';
                $numero = $this->getProximoNumero($venda->empresa, $serie, $modelo);

                $nfeRecord = Nfe::create([
                    'empresa_id' => $venda->empresa->id,
                    'venda_id' => $venda->id,
                    'status' => 'processando',
                    'ambiente' => $this->configArray['tpAmb'],
                    'serie' => $serie,
                    'modelo' => $modelo,
                    'numero_nfe' => $numero,
                ]);

                // Monta o XML com tpEmis = 1 (Normal)
                $this->buildHeader($venda->empresa, $venda, $numero, $serie, 1);
                $this->buildEmitter($venda->empresa);
                $this->buildRecipient($venda);
                $this->buildProducts($venda);
                $this->buildPayments($venda);
                $this->buildTransport($venda);
                $this->buildTotals();

                $xml = $this->nfe->getXML();
                $errors = $this->nfe->getErrors();
                if (count($errors) > 0) throw new \Exception("Erros de validação do XML: " . implode(', ', $errors));

                $chave = $this->nfe->getChave();
                $nfeRecord->update(['chave_acesso' => $chave]); // Salva a chave antes de enviar
                
                $xmlAssinado = $this->tools->signNFe($xml);
                
                $protocolo = $this->tools->sefazEnviaLote([$xmlAssinado], $nfeRecord->id, 1);
                
                $stProt = new Standardize($protocolo);
                $stdProt = $stProt->toStd();
                $cStat = $stdProt->protNFe->infProt->cStat ?? $stdProt->cStat ?? null;
                $xMotivo = $stdProt->protNFe->infProt->xMotivo ?? $stdProt->xMotivo ?? 'Motivo não especificado.';
                
                if (in_array($cStat, ['100', '150'])) {
                    $this->handleSuccess($protocolo, $xmlAssinado, $nfeRecord, $chave);
                    $venda->update(['nfe_chave_acesso' => $chave]);
                    DB::commit();
                    // Agora esta linha está correta, incluindo a danfeUrl
                    return ['success' => true, 'mode' => 'online', 'message' => "NFC-e #{$nfeRecord->numero_nfe} autorizada!", 'chave' => $chave, 'danfeUrl' => $nfeRecord->danfeUrl];
                } else {
                    throw new \Exception("[{$cStat}] {$xMotivo}");
                }

            } catch (\Exception $e) {
                DB::rollBack();
                if (str_contains($e->getMessage(), '[539]')) { // Rejeição: Duplicidade de NF-e
                    sleep(1);
                    continue;
                }
                throw $e; // Propaga a exceção para ser tratada pelo método 'emitir'
            }
        }
        return ['success' => true, 'mode' => 'online', 'message' => "NFC-e #{$nfeRecord->numero_nfe} autorizada!", 'chave' => $chave, 'danfeUrl' => $nfeRecord->danfeUrl];
    }

    private function emitirEmContingencia(Venda $venda, string $justificativa)
    {
        DB::beginTransaction();
        try {
            $this->nfe = new Make();
            $empresa = $venda->empresa;
            $this->bootstrap($empresa);

            $serie = 1; 
            $modelo = '65';
            $numero = $this->getProximoNumero($empresa, $serie, $modelo);

            // Monta o XML com tpEmis = 9 (Contingência Offline)
            $this->buildHeader($empresa, $venda, $numero, $serie, 9, $justificativa);
            $this->buildEmitter($empresa);
            $this->buildRecipient($venda);
            $this->buildProducts($venda);
            $this->buildPayments($venda);
            $this->buildTransport($venda);
            $this->buildTotals();
            
            $xml = $this->nfe->getXML();
            $errors = $this->nfe->getErrors();
            if (count($errors) > 0) throw new \Exception("Erros de validação do XML: " . implode(', ', $errors));
            
            $chave = $this->nfe->getChave();
            $xmlAssinado = $this->tools->signNFe($xml);

            $anoMes = date('Y-m');
            $pathXml = "nfe/xml/{$anoMes}/{$chave}.xml";
            Storage::disk('private')->put($pathXml, $xmlAssinado);

            $danfe = new Danfce($xmlAssinado);
            $pdf = $danfe->render();
            $pathDanfe = "nfe/danfe/{$anoMes}/{$chave}.pdf";
            Storage::disk('private')->put($pathDanfe, $pdf);

            $nfeRecord = Nfe::create([
                'empresa_id' => $empresa->id,
                'venda_id' => $venda->id,
                'status' => 'contingencia_pendente',
                'ambiente' => $this->configArray['tpAmb'],
                'serie' => $serie,
                'modelo' => $modelo,
                'numero_nfe' => $numero,
                'chave_acesso' => $chave,
                'caminho_xml' => $pathXml,
                'caminho_danfe' => $pathDanfe,
                'justificativa_contingencia' => $justificativa
            ]);

            $venda->update(['nfe_chave_acesso' => $chave]);
            DB::commit();
             
            $danfeUrl = URL::temporarySignedRoute('nfe.danfe', now()->addMinutes(5), ['nfe' => $nfeRecord->id]);
            return ['success' => true, 'mode' => 'contingencia', 'message' => "Venda salva em contingência!", 'chave' => $chave, 'danfeUrl' => $danfeUrl];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cartaCorrecao(Nfe $nfe, string $correcao): array
    {
        // Sua função original - Mantida
        DB::beginTransaction();
        try {
            $this->bootstrap($nfe->empresa);
            $response = $this->tools->sefazCCe($nfe->chave_acesso, $correcao, $nfe->cce_sequencia_evento);
            $st = new Standardize($response);
            $std = $st->toStd();
    
            if (in_array($std->cStat, ['135', '128'])) {
                $nfe->increment('cce_sequencia_evento');
                $this->handleCceSuccess($nfe, $response);
                DB::commit();
                $message = ($std->cStat == 128) ? 'processada com sucesso!' : 'emitida com sucesso!';
                return ['success' => true, 'message' => "Carta de Correção {$message}"];
            } else {
                throw new \Exception("[{$std->cStat}] {$std->xMotivo}");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function handleCceSuccess(Nfe $nfe, string $responseXml)
    {
        // Sua função original - Mantida
        try {
            $chave = $nfe->chave_acesso;
            $sequencia = $nfe->cce_sequencia_evento;
            $anoMes = date('Y-m');
            $pathXmlCce = "nfe/xml/{$anoMes}/{$chave}-cce-{$sequencia}.xml";
            Storage::disk('private')->put($pathXmlCce, $responseXml);
            $xmlAutorizado = Storage::disk('private')->get($nfe->caminho_xml);
            $danfe = new Danfe($xmlAutorizado);
            $danfe->addEvento($responseXml);
            $pdf = $danfe->render();
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
       
        // Sua função original - Mantida
        $stProt = new Standardize($protocolo);
        $stdProt = $stProt->toStd();
        $numeroProtocolo = $stdProt->protNFe->infProt->nProt ?? null;
        $xmlAutorizado = Complements::toAuthorize($xmlAssinado, $protocolo);
        $anoMes = date('Y-m');
        $pathXml = "nfe/xml/{$anoMes}/{$chave}.xml";
        Storage::disk('private')->put($pathXml, $xmlAutorizado);
        $danfe = new Danfce($xmlAutorizado);
        $pdf = $danfe->render();
        $pathDanfe = "nfe/danfe/{$anoMes}/{$chave}.pdf";
        Storage::disk('private')->put($pathDanfe, $pdf);
        $nfeRecord->update([
            'status' => 'autorizada',
            'chave_acesso' => $chave,
            'protocolo_autorizacao' => $numeroProtocolo,
            'caminho_xml' => $pathXml,
            'caminho_danfe' => $pathDanfe,
        ]);

        $nfeRecord->danfeUrl = URL::temporarySignedRoute('nfe.danfe', now()->addMinutes(5), ['nfe' => $nfeRecord->id]);
    }

    // ==================================================================
    // FUNÇÃO 'GETPROXIMONUMERO' ATUALIZADA
    // - Substitui a antiga 'getNextNFeNumber'
    // - Agora separa a numeração de NFe (55) e NFCe (65)
    // ==================================================================
    private function getProximoNumero(Empresa $empresa, int $serie, string $modelo): int
    {
        $max = Nfe::where('empresa_id', $empresa->id)
                  ->where('serie', $serie)
                  ->where('modelo', $modelo)
                  ->max('numero_nfe');

        if ($max) {
            return $max + 1;
        }

        $campoNumero = ($modelo == '65') ? 'nfce_proximo_numero' : 'nfe_proximo_numero';
        $numeroInicial = $empresa->{$campoNumero} ?? 1;

        return (int)$numeroInicial;
    }

    private function buildHeader(Empresa $empresa, Venda $venda, int $numero, int $serie, int $tpEmis = 1, string $justificativa = null)
    {
        $std_infNFe = new stdClass();
        $std_infNFe->versao = '4.00';
        $this->nfe->taginfNFe($std_infNFe);
        $std_ide = new stdClass();
        $std_ide->cUF = $empresa->codigo_uf;
        $std_ide->cNF = rand(10000000, 99999999);
        $std_ide->natOp = 'VENDA AO CONSUMIDOR';
        $std_ide->mod = 65;
        $std_ide->serie = $serie;
        $std_ide->nNF = $numero;
        $std_ide->dhEmi = date("Y-m-d\TH:i:sP");
        $std_ide->tpNF = 1;
        $std_ide->idDest = 1;
        $std_ide->cMunFG = $empresa->codigo_municipio;
        $std_ide->tpImp = 4;
        $std_ide->tpEmis = $tpEmis; // Usando o parâmetro
        $std_ide->tpAmb = $this->configArray['tpAmb'];
        $std_ide->finNFe = 1;
        $std_ide->indFinal = 1;
        $std_ide->indPres = 1;

        // Se estiver em contingência, adiciona as tags obrigatórias
        if ($tpEmis == 9) {
            $std_ide->dhCont = date("Y-m-d\TH:i:sP"); // Data e hora da entrada em contingência
            $std_ide->xJust = $justificativa;         // Justificativa
        }

        $std_ide->procEmi = 0;
        $std_ide->verProc = 'Sistema ERP 1.0';
        $this->nfe->tagide($std_ide);
    }
    
    private function buildEmitter(Empresa $empresa)
    {
        // Sua função original - Mantida
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
    
        // Se não houver cliente ou se o CPF/CNPJ for vazio, não adicionamos a tag <dest>
        // Isso é permitido e comum na NFC-e.
        if (!$cliente || empty(preg_replace('/[^0-9]/', '', $cliente->cpf_cnpj))) {
            // Para NFC-e, é opcional identificar o consumidor para valores baixos.
            // Apenas retornamos sem fazer nada.
            return; 
        }
    
        // Se houver cliente com documento, o código original continua válido.
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cliente->cpf_cnpj);
        
        $std = new stdClass();
        $std->xNome = $cliente->nome;
        
        if (strlen($cpfCnpj) == 14) {
            $std->CNPJ = $cpfCnpj;
            // Para NFC-e, se há CNPJ, indIEDest deve ser 1 (Contribuinte ICMS) ou 9 (Não contribuinte)
            $std->indIEDest = $cliente->ie ? '1' : '9'; 
            if (!empty($cliente->ie)) {
                $std->IE = preg_replace('/[^0-9]/', '', $cliente->ie);
            }
        } else {
            $std->CPF = $cpfCnpj;
            // Para CPF, indIEDest é sempre 9
            $std->indIEDest = '9';
        }
        
        // Opcional: Adicionar email para envio automático
        if (!empty($cliente->email)) {
           $std->email = $cliente->email;
        }
    
        $this->nfe->tagdest($std);
    
        // Endereço do destinatário só é obrigatório em entregas a domicílio (o que não é o caso padrão da NFC-e)
        // Portanto, podemos omitir a tag <enderDest> na maioria dos casos para simplificar.
    }
    
    private function buildProducts(Venda $venda)
    {
        // Sua função original - Mantida
        $empresa = $venda->empresa;
        $ufDestino = optional($venda->cliente)->estado ?? $empresa->uf;
        $regraTributariaService = new RegraTributariaService();

        foreach ($venda->items as $i => $item) {
            $produto = $item->produto;
            $cfop = $item->cfop ?? '5102';
            if (empty($cfop)) {
                throw new \Exception("O produto '{$produto->nome}' está sem CFOP definido.");
            }
            $regra = $regraTributariaService->findRule($cfop, $empresa, $ufDestino);
            if (!$regra) {
                throw new \Exception("Nenhuma regra tributária para '{$produto->nome}' (CFOP {$cfop}, Destino: {$ufDestino}).");
            }
            $impostos = $regraTributariaService->aplicarRegra($regra, $item);

            $std = new stdClass();
            $std->item = $i + 1;
            $std->cProd = $produto->id;
            $std->cEAN = $produto->codigo_barras ?: 'SEM GTIN';
            $std->xProd = $produto->nome;
            $std->NCM = $produto->dadosFiscais->ncm ?? null;
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

            $stdImposto = new stdClass();
            $stdImposto->item = $i + 1;
            $this->nfe->tagimposto($stdImposto);

            $std_icms = $impostos->ICMS;
            $std_icms->item = $i + 1;
            if ($empresa->crt == 1) {
                $this->nfe->tagICMSSN($std_icms);
            } else {
                $metodoIcms = 'tagICMS' . $std_icms->CST;
                 if (method_exists($this->nfe, $metodoIcms)) {
                    $this->nfe->{$metodoIcms}($std_icms);
                } else {
                    $this->nfe->tagICMS00($std_icms);
                }
            }
            $std_pis = $impostos->PIS;
            $std_pis->item = $i + 1;
            $this->nfe->tagPIS($std_pis);
            $std_cofins = $impostos->COFINS;
            $std_cofins->item = $i + 1;
            $this->nfe->tagCOFINS($std_cofins);
        }
    }

    private function handleCancelSuccess(Nfe $nfe, string $justificativa, string $responseXml)
    {
        // Sua função original - Mantida
        $nfe->update(['status' => 'cancelada', 'justificativa_cancelamento' => $justificativa]);
        $xmlAutorizado = Storage::disk('private')->get($nfe->caminho_xml);
        $xmlCancelado = Complements::cancelRegister($xmlAutorizado, $responseXml);
        Storage::disk('private')->put($nfe->caminho_xml, $xmlCancelado);
    }

    private function buildTotals()
    {
        // Sua função original - Mantida
        $this->nfe->tagICMSTot(new stdClass());
    }

    private function buildTransport(Venda $venda)
    {
        // Sua função original - Mantida
        $std = new stdClass();
        $std->modFrete = 9;
        $this->nfe->tagtransp($std);
    }


    public function enviarNotasEmContingencia()
    {
        $notasPendentes = Nfe::where('status', 'contingencia_pendente')->get();

        if ($notasPendentes->isEmpty()) {
            return ['success' => true, 'message' => 'Nenhuma NFC-e pendente de contingência.'];
        }

        foreach ($notasPendentes as $nfeRecord) {
            try {
                $this->bootstrap($nfeRecord->empresa);
                
                $statusServico = $this->tools->sefazStatus();
                $st = new Standardize($statusServico);
                $std = $st->toStd();
                if ($std->cStat != '107') {
                    Log::info("SEFAZ ainda fora do ar ({$std->xMotivo}). Pulando envio da chave: {$nfeRecord->chave_acesso}");
                    continue; 
                }

                $xmlAssinado = Storage::disk('private')->get($nfeRecord->caminho_xml);
                $protocolo = $this->tools->sefazEnviaLote([$xmlAssinado], $nfeRecord->id, 1);
                
                $stProt = new Standardize($protocolo);
                $stdProt = $stProt->toStd();
                $cStat = $stdProt->protNFe->infProt->cStat ?? $stdProt->cStat ?? null;

                if (in_array($cStat, ['100', '150'])) {
                    $this->handleSuccess($protocolo, $xmlAssinado, $nfeRecord, $nfeRecord->chave_acesso);
                    Log::info("NFC-e em contingência autorizada com sucesso: {$nfeRecord->chave_acesso}");
                } else {
                    $xMotivo = $stdProt->protNFe->infProt->xMotivo ?? $stdProt->xMotivo ?? 'Motivo não especificado.';
                    $nfeRecord->update(['status' => 'erro', 'motivo_rejeicao' => "[{$cStat}] {$xMotivo}"]);
                    Log::error("Rejeição ao enviar NFC-e em contingência: {$nfeRecord->chave_acesso} - Motivo: {$xMotivo}");
                }
            } catch (\Exception $e) {
                Log::error("Erro crítico ao enviar NFC-e em contingência (Chave: {$nfeRecord->chave_acesso}): " . $e->getMessage());
                continue;
            }
        }
        
        return ['success' => true, 'message' => 'Processo de envio em contingência finalizado.'];
    }

    public function cancelar(Nfe $nfe, string $justificativa): array
    {
        // Sua função original - Mantida
        try {
            $this->bootstrap($nfe->empresa);
            $chave = $nfe->chave_acesso;
            $protocolo = $nfe->protocolo_autorizacao;
            $sequenciaEvento = 1;
            $dhEvento = new \DateTime("now");
            $response = $this->tools->sefazCancela($chave, $justificativa, $protocolo, $dhEvento, $sequenciaEvento);
    
            if (empty($response)) {
                throw new \Exception("A SEFAZ não retornou resposta.");
            }
            $st = new \NFePHP\NFe\Common\Standardize($response);
            $std = $st->toStd();
            $respostaEvento = $std->retEvento->infEvento ?? $std;
            $resultadoSefaz = $this->tratarRespostaSefaz($respostaEvento);
            
            if ($resultadoSefaz['status'] === 'sucesso') {
                $this->handleCancelSuccess($nfe, $justificativa, $response);
                return ['success' => true, 'message' => 'NF-e cancelada com sucesso!'];
            } else {
                throw new \Exception($resultadoSefaz['mensagem']);
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    // ==================================================================
    // FUNÇÃO 'BUILDPAYMENTS' ATUALIZADA
    // - Agora trata Cartão (03, 04), "Outros" (99) e os demais
    // ==================================================================
    private function buildPayments(Venda $venda)
    {
        $this->nfe->tagpag(new stdClass());
        
        if ($venda->pagamentos->isEmpty()) {
            $det = new stdClass();
            $det->tPag = '01';
            $det->vPag = number_format($venda->total, 2, '.', '');
            $this->nfe->tagdetPag($det);
            return;
        }
    
        foreach ($venda->pagamentos as $pagamento) {
            $codigoPagamento = (string) $pagamento->formaPagamento->codigo_sefaz;
    
            // ================== ABORDAGEM CORRETA E FINAL ==================
            
            // Passo 1: Cria o grupo <detPag> com as informações básicas
            $det = new stdClass();
            $det->tPag = $codigoPagamento;
            $det->vPag = number_format($pagamento->valor, 2, '.', '');
            
            // Passo 2: A função tagdetPag RETORNA o nó XML criado. Nós o salvamos em uma variável.
            $detPagNode = $this->nfe->tagdetPag($det); 
    
            // Passo 3: Se for cartão ou PIX, usamos a variável $detPagNode para adicionar o filho <card>
            if (in_array($codigoPagamento, ['03', '04', '17'])) { 
                $dom = $this->nfe->dom;

                $cardNode = $dom->createElement('card');
                $detPagNode->appendChild($cardNode);

                $cardNode->appendChild($dom->createElement('tpIntegra', '2'));
               /*
                $cardNode->appendChild($dom->createElement('CNPJ', ''));
                $cardNode->appendChild($dom->createElement('tBand', ''));
                $cardNode->appendChild($dom->createElement('cAut', ''));
                */
            }
            // ====================================================================
        }
    }
}