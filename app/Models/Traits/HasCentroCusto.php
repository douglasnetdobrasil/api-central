<?php

namespace App\Models\Traits;

use App\Models\LancamentoCentroCusto;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCentroCusto
{
    /**
     * Relacionamento: Retorna todos os rateios de centro de custo para este lançamento.
     */
    public function centrosCusto(): MorphMany
    {
        return $this->morphMany(LancamentoCentroCusto::class, 'lancamento');
    }

    /**
     * Lógica para salvar/atualizar o rateio de centros de custo.
     *
     * @param array $rateios Ex: [['centro_custo_id' => 1, 'valor' => 50.00], ['centro_custo_id' => 2, 'valor' => 50.00]]
     * @return void
     */
    public function ratear(array $rateios): void
    {
        // 1. Remove o rateio antigo para garantir consistência
        $this->centrosCusto()->delete();

        // 2. Prepara os novos dados para inserção
        $dadosRateio = [];
        foreach ($rateios as $rateio) {
            // Validação mínima para garantir que os dados essenciais existem
            if (!empty($rateio['centro_custo_id']) && isset($rateio['valor'])) {
                $dadosRateio[] = new LancamentoCentroCusto([
                    'centro_custo_id' => $rateio['centro_custo_id'],
                    'valor' => $rateio['valor'],
                    // Adicionar lógica de cálculo de percentual se necessário
                ]);
            }
        }
        
        // 3. Salva o novo rateio
        if (!empty($dadosRateio)) {
            $this->centrosCusto()->saveMany($dadosRateio);
        }
    }
}