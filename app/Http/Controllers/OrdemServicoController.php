<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class OrdemServicoController extends Controller
{
    /**
     * Exibe uma lista paginada das Ordens de Serviço.
     */
    public function index()
    {
        // O with() otimiza a consulta, carregando os dados do cliente e do técnico
        // de uma só vez para evitar o problema de N+1 queries.
        $ordensServico = OrdemServico::with(['cliente', 'tecnico'])
                                     ->latest() // Ordena pelas mais recentes
                                     ->paginate(15); // Pagina o resultado

        // O próximo passo será criar esta view
        return view('ordens_servico.index', compact('ordensServico'));
    }

    /**
     * Mostra o formulário para criar uma nova Ordem de Serviço.
     */
    public function create()
    {
        // Precisamos enviar para a view a lista de clientes e técnicos
        // para preencher os campos <select> do formulário.
        $clientes = Cliente::orderBy('nome')->get();
        $tecnicos = User::orderBy('name')->get(); // Assumindo que técnicos são usuários

        // O próximo passo será criar esta view
        return view('ordens_servico.create', compact('clientes', 'tecnicos'));
    }

    /**
     * Salva uma nova Ordem de Serviço no banco de dados.
     */
    public function store(Request $request)
    {
        // AQUI ESTÁ A CORREÇÃO: Adicionar 'equipamento' e 'numero_serie' à validação.
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:Aberta,Aguardando Aprovação,Aprovada,Em Execução,Aguardando Peças,Concluída,Faturada,Cancelada',
            'data_previsao_conclusao' => 'nullable|date',
            'equipamento' => 'required|string|max:255', 
            'numero_serie' => 'nullable|string|max:100', 
            'defeito_relatado' => 'required|string',
            'laudo_tecnico' => 'nullable|string',
        ]);
        
        // O restante do seu código permanece igual...
        $validatedData['empresa_id'] = auth()->user()->empresa_id;
        $validatedData['tecnico_id'] = $validatedData['tecnico_id'] ?? auth()->id();

        OrdemServico::create($validatedData);

        return redirect()->route('ordens-servico.index')
                         ->with('success', 'Ordem de Serviço criada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma Ordem de Serviço específica.
     * O Laravel injeta o objeto $ordemServico automaticamente pelo ID na URL.
     */
    public function show(OrdemServico $ordemServico)
    {
        // Carrega os relacionamentos de itens para serem exibidos na view de detalhes
        $ordemServico->load(['cliente', 'tecnico', 'produtos.produto', 'servicos.servico', 'historico.usuario']);
        
        // O próximo passo será criar esta view
        return view('ordens_servico.show', compact('ordemServico'));
    }

    /**
     * Mostra o formulário para editar uma Ordem de Serviço existente.
     */
    public function edit(OrdemServico $ordemServico)
    {
        // Lógica similar ao create(), mas passando também a OS que está sendo editada
        $clientes = Cliente::orderBy('nome')->get();
        $tecnicos = User::orderBy('name')->get();

        // O próximo passo será criar esta view
        return view('ordens_servico.edit', compact('ordemServico', 'clientes', 'tecnicos'));
    }

    /**
     * Atualiza uma Ordem de Serviço no banco de dados.
     */
    public function update(Request $request, OrdemServico $ordemServico)
    {
        // CORRIJA A VALIDAÇÃO AQUI TAMBÉM
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'status' => 'required|string|in:Aberta,Aguardando Aprovação,Aprovada,Em Execução,Aguardando Peças,Concluída,Faturada,Cancelada',
            'data_previsao_conclusao' => 'nullable|date',
            'equipamento' => 'required|string|max:255', // <-- LINHA FALTANTE
            'numero_serie' => 'nullable|string|max:100', // <-- LINHA FALTANTE (OPCIONAL)
            'defeito_relatado' => 'required|string',
            'laudo_tecnico' => 'nullable|string',
        ]);

        $ordemServico->update($validatedData);

        return redirect()->route('ordens-servico.show', $ordemServico)
                         ->with('success', 'Ordem de Serviço atualizada com sucesso!');
    }
    /**
     * Remove uma Ordem de Serviço do banco de dados.
     */
    public function destroy(OrdemServico $ordemServico)
    {
        try {
            $ordemServico->delete();
            return redirect()->route('ordens-servico.index')
                             ->with('success', 'Ordem de Serviço excluída com sucesso!');
        } catch (\Exception $e) {
            // Tratamento de erro caso a exclusão falhe (ex: por restrições de chave estrangeira)
            return redirect()->back()
                             ->with('error', 'Não foi possível excluir a Ordem de Serviço. Verifique as dependências.');
        }
    }
}