<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuporteChamado;
use App\Models\OrdemServico;
use Illuminate\Http\Request; // <-- ADICIONE O use Request
use Illuminate\Support\Facades\Auth;
use App\Services\OrdemServicoService;
use App\Models\ClienteEquipamento;
use Illuminate\Validation\Rule;

class SuporteChamadoController extends Controller
{
    protected $osService;

    public function __construct(OrdemServicoService $osService)
    {
        $this->osService = $osService;
    }

    /**
     * ==========================================================
     * ||||||||||||||||||| MÉTODO 'index' ATUALIZADO |||||||||||||||||||
     * ==========================================================
     * Busca chamados separados para as abas e adiciona filtros.
     */
    public function index(Request $request)
    {
        // Define os status principais para as abas (sem alteração)
        $abasStatus = [
            'novos' => ['Aberto'],
            'meus' => ['Em Atendimento'],
            'aguardando_cliente' => ['Aguardando Cliente'],
            'aguardando_atendimento' => ['Aguardando Atendimento'],
            'meus_resolvidos' => ['Resolvido Online']
        ];

        $chamadosPorAba = [];
        // Agora precisamos buscar o equipamento junto
        $queryBase = SuporteChamado::with(['cliente', 'tecnico', 'equipamento']); // <-- ADICIONADO 'equipamento'

        // ===== NOVOS FILTROS =====
        if ($request->filled('cliente_id')) {
            $queryBase->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('prioridade')) {
            $queryBase->where('prioridade', $request->prioridade);
        }
        // Filtro por Equipamento (busca na descrição do equipamento relacionado)
        if ($request->filled('equipamento_search')) {
            $searchTerm = '%' . $request->equipamento_search . '%';
            $queryBase->whereHas('equipamento', function ($q) use ($searchTerm) {
                $q->where('descricao', 'like', $searchTerm)
                  ->orWhere('numero_serie', 'like', $searchTerm);
            });
            // Opcional: buscar também no campo 'equipamento' da OS (caso não linkado)
            // $queryBase->orWhere('equipamento', 'like', $searchTerm); // Descomente se quiser incluir
        }
        // Filtro por Problema (busca no título ou descrição)
        if ($request->filled('problema_search')) {
             $searchTerm = '%' . $request->problema_search . '%';
             $queryBase->where(function ($q) use ($searchTerm) {
                $q->where('titulo', 'like', $searchTerm)
                  ->orWhere('descricao_problema', 'like', $searchTerm);
             });
        }
        // ===== FIM DOS NOVOS FILTROS =====


        foreach ($abasStatus as $chave => $statusArray) {
            // ... (lógica existente para filtrar por status e técnico) ...
             $query = clone $queryBase;
            $query->whereIn('status', $statusArray);

            if ($chave === 'meus' || $chave === 'meus_resolvidos') {
                $query->where('tecnico_atribuido_id', Auth::id());
            } elseif ($chave === 'novos') {
                 $query->whereNull('tecnico_atribuido_id');
            }

            // Ordenação (sem alteração)
             if (in_array($chave, ['novos', 'meus', 'aguardando_atendimento'])) {
                $query->orderByRaw("FIELD(prioridade, 'Urgente', 'Alta', 'Média', 'Baixa')")
                      ->orderBy('created_at', 'asc');
            } elseif ($chave === 'meus_resolvidos') {
                 $query->latest('data_resolucao');
            } else {
                $query->latest('updated_at');
            }


            $chamadosPorAba[$chave] = $query->get();
        }

        // Busca todos os chamados para a aba "Todos" (com paginação)
        // A query base já tem os filtros aplicados
        $queryTodos = clone $queryBase;
        $queryTodos->orderByRaw("FIELD(prioridade, 'Urgente', 'Alta', 'Média', 'Baixa')")
                   ->orderBy('created_at', 'asc');
        $todosChamados = $queryTodos->paginate(20)->withQueryString();

        // Dados para os filtros (sem alteração)
        $clientes = \App\Models\Cliente::orderBy('nome')->pluck('nome', 'id');
        $prioridades = ['Urgente', 'Alta', 'Média', 'Baixa'];

        return view('admin.chamados.index', compact(
            'chamadosPorAba',
            'todosChamados',
            'clientes',
            'prioridades',
            'request'
        ));
    }

    public function show(SuporteChamado $chamado)
    {
        // Pré-carrega mais relacionamentos para a view
        $chamado->load(
            'cliente', 
            'equipamento', 
            'tecnico', 
            'ordemServico', 
            'mensagens.user', // User da mensagem (técnico)
            'mensagens.cliente', // Cliente da mensagem
            'mensagens.anexos', // Anexos de cada mensagem
            'anexos' // Anexos gerais do chamado (se houver)
        );
        return view('admin.chamados.show', compact('chamado'));
    }

    public function atribuir(SuporteChamado $chamado)
    {
        // Verifica se já não está atribuído ou se o status permite
        if ($chamado->tecnico_atribuido_id || $chamado->status != 'Aberto') {
             return redirect()->back()->with('error', 'Este chamado não pode ser atribuído no momento.');
        }

        $user = Auth::user();
        $chamado->update([
            'tecnico_atribuido_id' => $user->id,
            'status' => 'Em Atendimento',
        ]);

        $chamado->mensagens()->create([
            'user_id' => $user->id,
            'tipo' => 'Log',
            'mensagem' => 'Chamado atribuído a: ' . $user->name,
        ]);

        return redirect()->back()->with('success', 'Chamado atribuído a você!');
    }

    public function responder(Request $request, SuporteChamado $chamado)
    {
        $validated = $request->validate([
            'mensagem' => 'required|string|min:5',
            'interno' => 'nullable|boolean',
            'resp_anexos' => 'nullable|array', // Valida anexos da resposta
            'resp_anexos.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120'
        ]);

        $user = Auth::user();
        $interno = $request->has('interno');

        // Cria a mensagem
        $mensagem = $chamado->mensagens()->create([
            'user_id' => $user->id,
            'mensagem' => $validated['mensagem'],
            'tipo' => 'Comentário',
            'interno' => $interno,
        ]);

        // Salva anexos da mensagem, se houver
        if ($request->hasFile('resp_anexos')) {
            foreach ($request->file('resp_anexos') as $file) {
                // Salva em storage/app/public/chamados/[chamado_id]/[mensagem_id]/arquivo.jpg
                $caminho = $file->store('public/chamados/' . $chamado->id . '/' . $mensagem->id);

                $mensagem->anexos()->create([
                    'chamado_id' => $chamado->id, // Redundante mas útil
                    'caminho_arquivo' => $caminho,
                    'nome_original' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Se não for nota interna, muda o status para o cliente ver
        // A menos que o técnico já esteja resolvendo/fechando
        if (!$interno && !in_array($chamado->status, ['Resolvido Online', 'Fechado'])) {
            $chamado->update(['status' => 'Aguardando Cliente']);
        }

        return redirect()->back()->with('success', $interno ? 'Nota interna adicionada!' : 'Resposta enviada ao cliente!');
    }

    public function salvarSolucao(Request $request, SuporteChamado $chamado)
    {
        // Impede alteração se já estiver fechado (opcional)
        // if ($chamado->status == 'Fechado') {
        //     return redirect()->back()->with('error', 'Não é possível alterar a solução de um chamado fechado.');
        // }

        $validated = $request->validate([
            'solucao_aplicada' => 'nullable|string|max:65000', // max para TEXT
        ]);

        $chamado->update(['solucao_aplicada' => $validated['solucao_aplicada']]);

        // Adiciona log (opcional)
        // $chamado->mensagens()->create([
        //     'user_id' => Auth::id(),
        //     'tipo' => 'Log',
        //     'mensagem' => "Solução aplicada foi atualizada por " . Auth::user()->name . ".",
        // ]);

        return redirect()->back()->with('success', 'Solução do chamado salva com sucesso!');
    }

    public function mudarStatus(Request $request, SuporteChamado $chamado)
    {
        $statusOptions = ['Aberto', 'Em Atendimento', 'Aguardando Cliente', 'Aguardando Atendimento', 'Resolvido Online', 'Fechado'];
        $validated = $request->validate([
            'status' => ['required', Rule::in($statusOptions)],
        ]);

        $novoStatus = $validated['status'];
        $statusAntigo = $chamado->status;

        if ($novoStatus != $statusAntigo) {
            $updateData = ['status' => $novoStatus];
            // Se resolver ou fechar, registra a data
            if ($novoStatus == 'Resolvido Online' && !$chamado->data_resolucao) {
                $updateData['data_resolucao'] = now();
            }
            if ($novoStatus == 'Fechado' && !$chamado->data_fechamento) {
                $updateData['data_fechamento'] = now();
                // Se fechou sem resolver antes, marca resolvido também
                if (!$chamado->data_resolucao) $updateData['data_resolucao'] = now();
            }

            $chamado->update($updateData);

            // Adiciona log
            $chamado->mensagens()->create([
                'user_id' => Auth::id(),
                'tipo' => 'Log',
                'mensagem' => "Status alterado de '{$statusAntigo}' para '{$novoStatus}' por " . Auth::user()->name . ".",
            ]);

            return redirect()->back()->with('success', 'Status do chamado atualizado!');
        }

        return redirect()->back(); // Nenhuma mudança
    }

    public function mudarPrioridade(Request $request, SuporteChamado $chamado)
    {
        $prioridadeOptions = ['Baixa', 'Média', 'Alta', 'Urgente'];
        $validated = $request->validate([
            'prioridade' => ['required', Rule::in($prioridadeOptions)],
        ]);

        $novaPrioridade = $validated['prioridade'];
        $prioridadeAntiga = $chamado->prioridade;

        if ($novaPrioridade != $prioridadeAntiga) {
            $chamado->update(['prioridade' => $novaPrioridade]);

            // Adiciona log
            $chamado->mensagens()->create([
                'user_id' => Auth::id(),
                'tipo' => 'Log',
                'mensagem' => "Prioridade alterada de '{$prioridadeAntiga}' para '{$novaPrioridade}' por " . Auth::user()->name . ".",
            ]);
             return redirect()->back()->with('success', 'Prioridade do chamado atualizada!');
        }

         return redirect()->back(); // Nenhuma mudança
    }

    // ... (resto do controller: show, atribuir, responder, converterOS) ...
}