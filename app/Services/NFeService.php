<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\Empresa;
use App\Models\Nfe;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Config;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class NFeService
{
    private $config;
    private $tools;

    private function bootstrap(Empresa $empresa)
    {
        $this->config = null;
        $this->tools = null;

        $nfeConfig = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb'       => (int) $empresa->ambiente_nfe, // 1=Produção, 2=Homologação
            'razaosocial' => $empresa->razao_social,
            'siglaUF'     => $empresa->uf,
            'cnpj'        => preg_replace('/[^0-9]/', '', $empresa->cnpj),
            'schemes'     => 'PL_009_V4',
            'versao'      => '4.00',
            'tokenIBPT'   => '',
            'CSC'         => '',
            'CSCid'       => '',
        ];
        
        $configJson = json_encode($nfeConfig);
        $certificatePath = Storage::path($empresa->certificado_a1_path);

        $this->tools = new Tools($configJson, Certificate::readPfx(file_get_contents($certificatePath), $empresa->certificado_a1_password));
        $this->tools->model('55'); // Define o modelo de documento como NF-e (55)
    }

    public function emitir(Venda $venda)
    {
        DB::beginTransaction();
        try {
            $empresa = $venda->empresa;
            $venda->load('cliente', 'items.produto.dadosFiscais', 'pagamentos');

            $this->bootstrap($empresa);

            $nfe = new Make();
            
            // Cria um registro inicial na tabela nfes para controle
            $nfeRecord = Nfe::create([
                'empresa_id' => $empresa->id,
                'venda_id' => $venda->id,
                'status' => 'processando',
                'numero_nfe' => 0, 'serie' => 0, // Serão atualizados no buildHeader
                'ambiente' => $this->tools->getConfigs()['tpAmb'] == 1 ? 'producao' : 'homologacao',
            ]);

            // Montagem da NFe passo a passo
            $this->buildHeader($nfe, $empresa, $venda, $nfeRecord);
            $this->buildEmitter($nfe, $empresa);
            $this->buildRecipient($nfe, $venda);
            $this->buildProducts($nfe, $venda, $empresa);
            $this->buildTotals($nfe);
            $this->buildTransport($nfe);
            $this->buildPayments($nfe, $venda);

            // Monta a NFe, gera o XML e obtém a chave de acesso
            $xml = $nfe->montaNFe();
            $chave = $nfe->getChave();

            // Assina o XML
            $xmlAssinado = $this->tools->signNFe($xml);

            // Envia para a SEFAZ
            $response = $this->tools->sefazEnviaLote([$xmlAssinado], $nfeRecord->id);
            $st = new Standardize($response);
            $std = $st->toStd();
            
            // Verifica se o lote foi recebido com sucesso
            if ($std->cStat == '103' || $std->cStat == '104') {
                $recibo = $std->infRec->nRec;
                $protocol = $this->tools->sefazConsultaRecibo($recibo);
                $stProt = new Standardize($protocol);
                $stdProt = $stProt->toStd();

                if ($stdProt->cStat == '100') { // Autorizado o uso da NF-e
                    $this->handleSuccess($stdProt, $xmlAssinado, $nfeRecord, $venda, $chave);
                    DB::commit();
                    return ['success' => true, 'message' => "NF-e #{$nfeRecord->numero_nfe} autorizada com sucesso!", 'chave' => $chave];
                } else {
                    throw new \Exception("Erro no processamento do lote: [{$stdProt->cStat}] {$stdProt->xMotivo}");
                }
            } else {
                throw new \Exception("Erro no envio do lote para a SEFAZ: [{$std->cStat}] {$std->xMotivo}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($nfeRecord)) {
                $nfeRecord->update(['status' => 'erro', 'mensagem_erro' => $e->getMessage()]);
            }
            return ['success' => false, 'message' => "Erro ao emitir NFe: " . $e->getMessage()];
        }
    }

    private function handleSuccess($protocolo, $xmlAssinado, Nfe $nfeRecord, Venda $venda, $chave)
    {
        $xmlAutorizado = \NFePHP\NFe\Factories\Path::glue($xmlAssinado, $protocolo);
        
        $anoMes = date('Y-m');
        $pathXml = "nfe/xml/{$anoMes}/{$chave}.xml";
        Storage::put($pathXml, $xmlAutorizado);

        $danfe = new \NFePHP\DA\NFe\Danfe($xmlAutorizado);
        $danfe->monta();
        $pdf = $danfe->render();
        $pathDanfe = "nfe/danfe/{$anoMes}/{$chave}.pdf";
        Storage::put($pathDanfe, $pdf);

        $nfeRecord->update([
            'status' => 'autorizada',
            'chave_acesso' => $chave,
            'caminho_xml' => $pathXml,
            'caminho_danfe' => $pathDanfe,
        ]);
        
        $venda->update(['nfe_chave_acesso' => $chave]);
    }

    private function getNextNFeNumber(Empresa $empresa, int $serie): int
    {
        $ultimoNumero = Nfe::where('empresa_id', $empresa->id)
                           ->where('serie', $serie)
                           ->max('numero_nfe');
        return $ultimoNumero ? $ultimoNumero + 1 : 1;
    }

    private function buildHeader(Make &$nfe, Empresa $empresa, Venda $venda, Nfe &$nfeRecord)
    {
        $serie = 1;
        $numero = $this->getNextNFeNumber($empresa, $serie);
        $nfeRecord->update(['numero_nfe' => $numero, 'serie' => $serie]);

        $info = new stdClass();
        $info->versao = '4.00';
        $info->nNF = $numero; 
        $info->serie = $serie;
        $info->cMunFG = $empresa->codigo_municipio;
        $info->tpEmis = 1; $info->cDV = ''; $info->tpAmb = $this->tools->getConfigs()['tpAmb'];
        $info->finNFe = 1; $info->indFinal = 1; $info->indPres = 1; $info->procEmi = 0;
        $info->verProc = '1.0'; $info->dhEmi = date("Y-m-d\TH:i:sP");
        $info->dhSaiEnt = date("Y-m-d\TH:i:sP"); $info->tpNF = 1; $info->idDest = 1;
        $info->natOp = 'VENDA DE MERCADORIAS';
        $nfe->taginfNFe($info);
    }

    private function buildEmitter(Make &$nfe, Empresa $empresa) { /* ...código da versão anterior... */ }
    private function buildRecipient(Make &$nfe, Venda $venda) { /* ...código da versão anterior... */ }
    private function buildProducts(Make &$nfe, Venda $venda, Empresa $empresa) { /* ...código da versão anterior... */ }
    private function buildTotals(Make &$nfe) { /* ...código da versão anterior... */ }
    private function buildTransport(Make &$nfe) { /* ...código da versão anterior... */ }
    private function buildPayments(Make &$nfe, Venda $venda) { /* ...código da versão anterior... */ }
}