<?php

namespace App\Services;

use App\Models\RegraTributaria;
use App\Models\VendaItem;
use App\Models\Empresa;
use App\Models\Cliente;
use stdClass;

class RegraTributariaService
{
    /**
     * Encontra a regra tributária mais aplicável para uma determinada operação.
     * A busca é feita em cascata, da mais específica para a mais genérica.
     */
    public function findRule(string $cfop, Empresa $emitente, $destinatarioInfo): ?RegraTributaria
    {
        // ================== LÓGICA DE ADAPTAÇÃO (NOSSA CORREÇÃO) ==================
        $ufDestino = '';
        if ($destinatarioInfo instanceof Cliente) {
            // Se recebemos o objeto Cliente, pegamos o estado dele. (Cenário da NFe)
            $ufDestino = $destinatarioInfo->estado;
        } elseif (is_string($destinatarioInfo)) {
            // Se recebemos um texto, usamos ele diretamente como a UF. (Cenário da NFCe)
            $ufDestino = $destinatarioInfo;
        } else {
            // Se não for nenhum dos dois, usamos a UF da própria empresa como padrão.
            $ufDestino = $emitente->uf;
        }
        // =========================================================================

        $ufOrigem = $emitente->uf;
        $crtEmitente = $emitente->crt;
        $query = RegraTributaria::where('cfop', $cfop)->where('ativo', true);

        // ================== A SUA LÓGICA DE BUSCA (PERMANECE INTACTA) ==================
        // 1ª Tentativa: Regra super específica (CFOP + UFs + CRT)
        $rule = (clone $query)
            ->where('uf_origem', $ufOrigem)
            ->where('uf_destino', $ufDestino)
            ->where('crt_emitente', $crtEmitente)
            ->first();
        if ($rule) return $rule;

        // 2ª Tentativa: Sem o CRT (CFOP + UFs)
        $rule = (clone $query)
            ->where('uf_origem', $ufOrigem)
            ->where('uf_destino', $ufDestino)
            ->whereNull('crt_emitente')
            ->first();
        if ($rule) return $rule;

        // 3ª Tentativa: Apenas CFOP e CRT (válido para todo o Brasil)
         $rule = (clone $query)
            ->whereNull('uf_origem')
            ->whereNull('uf_destino')
            ->where('crt_emitente', $crtEmitente)
            ->first();
        if ($rule) return $rule;

        // 4ª Tentativa: Regra mais genérica (apenas CFOP)
        $rule = (clone $query)
            ->whereNull('uf_origem')
            ->whereNull('uf_destino')
            ->whereNull('crt_emitente')
            ->first();

        return $rule;
    }

    /**
     * Aplica uma regra a um item e retorna um objeto padronizado com os impostos.
     */
    public function aplicarRegra(RegraTributaria $regra, VendaItem $item): stdClass
    {
        $impostos = new stdClass();
        $baseCalculo = $item->subtotal_item;
    
        // --- ICMS ---
        $impostos->ICMS = new stdClass();
        $impostos->ICMS->orig = $regra->icms_origem;
        $impostos->ICMS->CSOSN = $regra->csosn;
        $impostos->ICMS->CST = $regra->icms_cst;
    
        if ($regra->csosn) { // Se for Simples Nacional
            // A biblioteca nfephp lida com a montagem do XML para CSOSN,
            // então geralmente só precisamos de passar o código.
        } else { // Se for Regime Normal
            switch ($regra->icms_cst) {
                case '00': // Tributado integralmente
                    $impostos->ICMS->modBC = $regra->icms_mod_bc ?? 3;
                    $impostos->ICMS->vBC = $baseCalculo;
                    $impostos->ICMS->pICMS = $regra->icms_aliquota;
                    $impostos->ICMS->vICMS = round($baseCalculo * ($regra->icms_aliquota / 100), 2);
                    break;
                case '20': // Com redução de base de cálculo
                    $impostos->ICMS->modBC = $regra->icms_mod_bc ?? 3;
                    $impostos->ICMS->pRedBC = $regra->icms_reducao_bc;
                    $impostos->ICMS->vBC = round($baseCalculo * (1 - ($regra->icms_reducao_bc / 100)), 2);
                    $impostos->ICMS->pICMS = $regra->icms_aliquota;
                    $impostos->ICMS->vICMS = round($impostos->ICMS->vBC * ($regra->icms_aliquota / 100), 2);
                    break;
                case '40': case '41': case '50':
                    // Isenta, Não tributada, Suspensão (não precisam de valores)
                    break;
                case '60': // ICMS cobrado anteriormente por ST
                    $impostos->ICMS->vBCSTRet = 0.00;
                    $impostos->ICMS->vICMSSTRet = 0.00;
                    break;
            }
        }
        
        // --- IPI, PIS, COFINS ---
        $impostos->IPI = new stdClass();
        $impostos->IPI->cEnq = $regra->ipi_codigo_enquadramento;
        $impostos->IPI->CST = $regra->ipi_cst;
        if ($regra->ipi_aliquota > 0) {
            $impostos->IPI->vBC = $baseCalculo;
            $impostos->IPI->pIPI = $regra->ipi_aliquota;
            $impostos->IPI->vIPI = round($baseCalculo * ($regra->ipi_aliquota / 100), 2);
        }
    
        $impostos->PIS = new stdClass();
        $impostos->PIS->CST = str_pad($regra->pis_cst ?? '01', 2, '0', STR_PAD_LEFT);
        $impostos->PIS->vBC = $regra->pis_aliquota > 0 ? $baseCalculo : 0.00;
        $impostos->PIS->pPIS = $regra->pis_aliquota;
        $impostos->PIS->vPIS = round($impostos->PIS->vBC * ($regra->pis_aliquota / 100), 2);
    
        $impostos->COFINS = new stdClass();
        $impostos->COFINS->CST = str_pad($regra->cofins_cst ?? '01', 2, '0', STR_PAD_LEFT);
        $impostos->COFINS->vBC = $regra->cofins_aliquota > 0 ? $baseCalculo : 0.00;
        $impostos->COFINS->pCOFINS = $regra->cofins_aliquota;
        $impostos->COFINS->vCOFINS = round($impostos->COFINS->vBC * ($regra->cofins_aliquota / 100), 2);
        
        return $impostos;
    }
}