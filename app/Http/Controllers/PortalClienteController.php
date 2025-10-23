<?php

namespace App\Http\Controllers;

use App\Models\SuporteChamado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClienteEquipamento; // Adicionado na etapa anterior
use Illuminate\Http\JsonResponse; // Adicionado na etapa anterior

class PortalClienteController extends Controller
{
    // Retorna o "auth" guard do cliente
    private function clienteAuth() {
        return Auth::guard('cliente')->user();
    }

    // dashboard: lista de chamados do cliente
    public function index(Request $request)
    {
        // Remova o dd() do ID do cliente que adicionamos antes, se ainda estiver lá.
    
        $query = $this->clienteAuth()->chamados()->latest();
    
        $statusFiltro = $request->input('status');
        if ($statusFiltro && $statusFiltro !== 'todos') {
            $query->where('status', $statusFiltro);
        }
    
        // ==========================================================
        // ||||||||||||||||||| A MUDANÇA ESTÁ AQUI |||||||||||||||||||
        // ==========================================================
        // Trocamos paginate(10) por get() para testar
        $chamados = $query->get();
        // ==========================================================
    
        $statusOptions = [
            'Aberto',
            'Em Atendimento',
            'Aguardando Cliente',
            'Resolvido Online',
            'Convertido em OS',
            'Fechado'
        ];
    
        // O dump agora mostrará uma Collection normal, não um Paginator
        // @dump($chamados) // Mantenha o dump na view por enquanto
    
        return view('portal_cliente.index', compact('chamados', 'statusOptions', 'statusFiltro'));
    }

    // Formulário para abrir novo chamado
    public function create()
    {
        $equipamentos = $this->clienteAuth()->equipamentos;
        return view('portal_cliente.create', compact('equipamentos'));
    }

    // Salvar o novo chamado
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'prioridade' => 'required|string|in:Baixa,Média,Alta,Urgente', // <-- VALIDAÇÃO ADICIONADA
            'descricao_problema' => 'required|string|min:20',
            'cliente_equipamento_id' => 'nullable|exists:cliente_equipamentos,id',
            'anexos' => 'nullable|array',
            'anexos.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120'
        ]);

        $chamado = SuporteChamado::create([
            'empresa_id' => $this->clienteAuth()->empresa_id,
            'cliente_id' => $this->clienteAuth()->id,
            'titulo' => $validated['titulo'],
            'prioridade' => $validated['prioridade'], // <-- CAMPO ADICIONADO AO CREATE
            'descricao_problema' => $validated['descricao_problema'],
            'cliente_equipamento_id' => $validated['cliente_equipamento_id'],
            'status' => 'Aberto',
        ]);

        // ... (lógica de upload de anexos) ...
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $file) {
                $caminho = $file->store('public/chamados/' . $chamado->id);
                $chamado->anexos()->create([
                    'caminho_arquivo' => $caminho,
                    'nome_original' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }


        return redirect()->route('portal.dashboard')
                         ->with('success', 'Chamado aberto com sucesso! Protocolo: ' . $chamado->protocolo);
    }

    // Ver a timeline do chamado
    public function show(SuporteChamado $chamado)
    {
        // Garante que o cliente só veja o chamado dele
        if ($chamado->cliente_id !== $this->clienteAuth()->id) {
            abort(403);
        }
        
        // ==========================================================
        // ||||||||||||||||||| A CORREÇÃO ESTÁ AQUI |||||||||||||||||||
        // ==========================================================
        // Adicionamos 'cliente' ao 'load'
        $chamado->load('cliente', 'mensagens.user', 'mensagens.cliente', 'anexos', 'equipamento');
        
        return view('portal_cliente.show', compact('chamado'));
    }

    // Cliente responde ao chamado
    public function responder(Request $request, SuporteChamado $chamado)
    {
        if ($chamado->cliente_id !== $this->clienteAuth()->id) { abort(403); }

        $validated = $request->validate(['mensagem' => 'required|string|min:5']);

        $chamado->mensagens()->create([
            'cliente_id' => $this->clienteAuth()->id,
            'mensagem' => $validated['mensagem'],
            'tipo' => 'Comentário',
        ]);
        
        $chamado->update(['status' => 'Aguardando Atendimento']); // Reabre para o técnico

        return redirect()->back()->with('success', 'Resposta enviada!');
    }
    
    // Salva um novo equipamento vindo do modal do portal do cliente.
    public function storeEquipamentoModal(Request $request): JsonResponse
    {
        $clienteLogado = $this->clienteAuth();

        $validatedData = $request->validate([
            'descricao' => 'required|string|max:255',
            'numero_serie' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
        ]);

        $validatedData['empresa_id'] = $clienteLogado->empresa_id;
        $validatedData['cliente_id'] = $clienteLogado->id;

        $equipamento = ClienteEquipamento::create($validatedData);
        $equipamento->texto = $equipamento->descricao . ($equipamento->numero_serie ? " (SN: {$equipamento->numero_serie})" : "");

        return response()->json($equipamento);
    }
}