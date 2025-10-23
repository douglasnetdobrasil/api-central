<?php
namespace App\Services;

use App\Models\OrdemServico;
use App\Models\ClienteEquipamento;
use App\Models\User;

class OrdemServicoService
{
    /**
     * Lógica de criação de OS, extraída do seu Controller.
     */
    public function criarOS(array $dados, User $usuarioLogado): OrdemServico
    {
        $dadosParaCriar = [
            'cliente_id' => $dados['cliente_id'],
            'tecnico_id' => $dados['tecnico_id'] ?? null,
            'status' => $dados['status'] ?? 'Aberta',
            'defeito_relatado' => $dados['defeito_relatado'],
            'cliente_equipamento_id' => $dados['cliente_equipamento_id'] ?? null,
            'data_previsao_conclusao' => $dados['data_previsao_conclusao'] ?? null,
            'equipamento' => $dados['equipamento'] ?? 'Não informado',
            'numero_serie' => $dados['numero_serie'] ?? null,
            
            'empresa_id' => $usuarioLogado->empresa_id,
            'data_entrada' => now(),
        ];
        
        // Lógica que você já usa no Controller
        if (!empty($dados['cliente_equipamento_id'])) {
            $equipamento = ClienteEquipamento::find($dados['cliente_equipamento_id']);
            if ($equipamento) {
                $dadosParaCriar['equipamento'] = $equipamento->descricao;
                $dadosParaCriar['numero_serie'] = $equipamento->numero_serie;
            }
        }

        $ordemServico = OrdemServico::create($dadosParaCriar);

        // Cria o histórico inicial (como você já faz)
        $ordemServico->historico()->create([
            'user_id' => $usuarioLogado->id,
            'descricao' => "OS Criada com status '{$ordemServico->status}'. " . ($dados['origem_chamado'] ?? ''),
        ]);
        
        return $ordemServico;
    }
}