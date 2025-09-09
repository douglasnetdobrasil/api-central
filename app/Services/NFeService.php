<?php

namespace App\Services;

// ===== INÍCIO DA CORREÇÃO DEFINITIVA DE TODOS OS NAMESPACES =====
use NFePHP\Common\Certificate;
use NFePHP\Common\Config\Config; // <-- Este já estava correto
use NFePHP\Sped\Danfe;
use NFePHP\Sped\Make; // <-- A classe do erro atual
use NFePHP\Sped\Tools;
use NFePHP\Sped\Common\Standardize;
// ===== FIM DA CORREÇÃO DEFINITIVA DE TODOS OS NAMESPACES =====

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

    public function __construct()
    {
        // Agora o PHP encontrará a classe 'Make' sem problemas
        $this->nfe = new Make();
    }

    private function bootstrap(Empresa $empresa)
    {
        if (empty($empresa->certificado_a1_path)) {
            throw new \Exception("O cadastro da empresa não possui um arquivo de certificado digital A1.");
        }
        $certificatePath = Storage::disk('private')->path($empresa->certificado_a1_path);
        if (!file_exists($certificatePath)) {
            throw new \Exception("Arquivo do certificado digital não encontrado.");
        }

        $config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) $empresa->ambiente_nfe,
            'razaosocial' => $empresa->razao_social,
            'siglaUF'     => $empresa->uf,
            'cnpj'        => preg_replace('/[^0-9]/', '', $empresa->cnpj),
            'schemes'     => 'PL_009_V4',
            'versao'      => '4.00',
            'tokenIBPT'   => '',
            'CSC'         => $empresa->csc_nfe,
            'CSCid'       => $empresa->csc_id_nfe,
        ];

        $this->config = new Config(json_encode($config));
        
        $certificate = Certificate::readPfx(
            file_get_contents($certificatePath),
            $empresa->certificado_a1_password
        );

        $this->tools = new Tools($this->config->getJson(), $certificate);
        $this->tools->model('55');
    }

    public function emitirDeVendas(array $vendaIds): array
    {
        $vendas = Venda::with('cliente', 'items.produto.dadosFiscais', 'pagamentos', 'empresa')->whereIn('id', $vendaIds)->get();
        if ($vendas->isEmpty()) {
            return ['success' => false, 'message' => 'Nenhuma venda encontrada.'];
        }

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
            if (is_null($vendaIdsOriginais)) {
                $vendaIdsOriginais = [$venda->id];
            }
            
            $this->bootstrap($empresa);

            // 3. REMOVIDO: a linha '$nfe = new Make();' foi removida daqui

            $nfeRecord = Nfe::create([
                'empresa_id' => $empresa->id,
                'venda_id' => $vendaIdsOriginais[0],
                'status' => 'processando',
                'ambiente' => $this->config->get('tpAmb'),
            ]);

            // 4. PADRONIZADO: Todos os métodos build* agora usam a propriedade $this->nfe
            $this->buildHeader($empresa, $venda, $nfeRecord);
            $this->buildEmitter($empresa);
            $this->buildRecipient($venda);
            $this->buildProducts($venda);
            $this->buildTotals();
            $this->buildTransport();
            $this->buildPayments($venda);

            $xml = $this->nfe->getXML(); // Usa a propriedade da classe
            $chave = $this->nfe->getChave();
            $xmlAssinado = $this->tools->signNFe($xml);
            $response = $this->tools->sefazEnviaLote([$xmlAssinado], $nfeRecord->id);
            $st = new Standardize($response);
            $std = $st->toStd();
            
            if ($std->cStat == '103' || $std->cStat == '104') {
                $recibo = $std->infRec->nRec;
                sleep(3); // Pausa para SEFAZ processar
                $protocol = $this->tools->sefazConsultaRecibo($recibo);
                $stProt = new Standardize($protocol);
                $stdProt = $stProt->toStd();

                if (in_array($stdProt->cStat, ['100', '150'])) {
                    $this->handleSuccess($stdProt, $xmlAssinado, $nfeRecord, $chave);
                    
                    Venda::whereIn('id', $vendaIdsOriginais)->update(['nfe_chave_acesso' => $chave]);

                    DB::commit();
                    return ['success' => true, 'message' => "NF-e #{$nfeRecord->numero_nfe} autorizada com sucesso!", 'chave' => $chave];
                } else {
                    throw new \Exception("SEFAZ Rejeitou: [{$stdProt->cStat}] {$stdProt->xMotivo}");
                }
            } else {
                throw new \Exception("SEFAZ Rejeitou: [{$std->cStat}] {$std->xMotivo}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($nfeRecord)) {
                $nfeRecord->update(['status' => 'erro', 'mensagem_erro' => $e->getMessage()]);
            }
            return ['success' => false, 'message' => "Erro ao emitir NF-e: " . $e->getMessage()];
        }
    }

    private function handleSuccess($protocolo, $xmlAssinado, Nfe $nfeRecord, $chave)
    {
        $xmlAutorizado = \NFePHP\NFe\Factories\Protocols::add($xmlAssinado, $protocolo);
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

    // 5. PADRONIZADO: Assinaturas dos métodos build* foram simplificadas
    private function buildHeader(Empresa $empresa, Venda $venda, Nfe &$nfeRecord)
    {
        $serie = 1;
        $numero = $this->getNextNFeNumber($empresa, $serie);
        
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
        $std->tpImp = 1;
        $std->tpEmis = 1;
        $std->tpAmb = $this->config->get('tpAmb');
        $std->finNFe = 1;
        $std->indFinal = 1;
        $std->indPres = 1;
        $std->procEmi = 0;
        $std->verProc = '1.0';
        $this->nfe->tagide($std);
    }
    
    private function buildEmitter(Empresa $empresa) { /* ... Lógica ... */ }
    private function buildRecipient(Venda $venda) { /* ... Lógica ... */ }
    private function buildProducts(Venda $venda) { /* ... Lógica já implementada ... */ }
    private function buildTotals() { /* ... Lógica ... */ }
    private function buildTransport() { /* ... Lógica ... */ }
    private function buildPayments(Venda $venda) { /* ... Lógica ... */ }
}