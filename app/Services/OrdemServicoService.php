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
            
            // ADICIONADO: Campo de rastreamento do Chamado
            'suporte_chamado_id' => $dados['suporte_chamado_id'] ?? null, 
            
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
        
        // Se a origem do chamado existir, garantimos que ela será usada no histórico.
        $origemChamado = $dados['origem_chamado'] ?? null;

        $ordemServico = OrdemServico::create($dadosParaCriar);

        // Cria o histórico inicial (ajustado para usar a variável local $origemChamado)
        $ordemServico->historico()->create([
            
            'user_id' => $usuarioLogado->id,
            'descricao' => "OS Criada com status '{$ordemServico->status}'. " . ($origemChamado ?? ''),
        ]);
        
        return $ordemServico;
    }
}
