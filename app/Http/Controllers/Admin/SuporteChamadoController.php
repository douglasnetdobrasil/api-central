<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuporteChamado;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\OrdemServicoService;
use App\Models\Cliente;
use App\Models\ClienteEquipamento;
use App\Models\User; // <-- Verifique se este 'use' está presente
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Necessário para a Query

class SuporteChamadoController extends Controller
{
    protected $osService;

    public function __construct(OrdemServicoService $osService)
    {
        $this->osService = $osService;
    }

    public function index(Request $request)
    {
        // Define os status principais para as abas
        $abasStatus = [
            'novos' => ['Aberto'],
            'meus' => ['Em Atendimento'],
            'aguardando_cliente' => ['Aguardando Cliente'],
            'aguardando_atendimento' => ['Aguardando Atendimento'],
            'meus_resolvidos' => ['Resolvido Online'],
            // ADICIONADO: Aba de Fechados
            'fechados' => ['Fechado', 'Convertido em OS']
        ];

        $chamadosPorAba = [];
        $queryBase = SuporteChamado::with(['cliente', 'tecnico', 'equipamento']);

        // ==========================================================
        // |||||||||||||||||||| FILTROS GERAIS ||||||||||||||||||||
        // ==========================================================
        
        if ($request->filled('protocolo')) {
            $queryBase->where('protocolo', 'like', '%' . $request->protocolo . '%');
        }
        
        if ($request->filled('tecnico_id')) {
            // Se for "Não Atribuído" (valor 0), filtra por nulo.
            if ($request->tecnico_id == 0) {
                 $queryBase->whereNull('tecnico_atribuido_id');
            } else {
                 $queryBase->where('tecnico_atribuido_id', $request->tecnico_id);
            }
        }
        
        // O filtro de status geral só é aplicado se NÃO estivermos na aba "Todos" 
        // ou se o filtro de status for especificamente preenchido para a aba "Todos"
        if ($request->filled('status') && $request->status !== '') {
            $queryBase->where('status', $request->status);
        }
        
        if ($request->filled('busca_texto')) {
             $searchTerm = '%' . $request->busca_texto . '%';
             $queryBase->where(function ($query) use ($searchTerm) {
                $query->where('titulo', 'like', $searchTerm)
                      ->orWhere('descricao_problema', 'like', $searchTerm)
                      ->orWhereHas('equipamento', function ($q) use ($searchTerm) {
                            $q->where('descricao', 'like', $searchTerm)
                              ->orWhere('numero_serie', 'like', $searchTerm);
                      });
             });
        }
        
        // ==========================================================
        // |||||||||||||||||||| NOVOS FILTROS DE EQUIPAMENTO ||||||||||||||||||||
        // ==========================================================
        
        if ($request->filled('cliente_id')) {
            $queryBase->where('cliente_id', $request->cliente_id);
        }
        
        if ($request->filled('cliente_equipamento_id')) {
             $queryBase->where('cliente_equipamento_id', $request->cliente_equipamento_id);
        }
        
        // ==========================================================
        // |||||||||||||||||||| FILTROS EXISTENTES (Prioridade) |||||||||||||||||||
        // ==========================================================
        
        if ($request->filled('prioridade')) {
            $queryBase->where('prioridade', $request->prioridade);
        }

        foreach ($abasStatus as $chave => $statusArray) {
             $query = clone $queryBase;
            $query->whereIn('status', $statusArray);

            if ($chave === 'meus' || $chave === 'meus_resolvidos') {
                $query->where('tecnico_atribuido_id', Auth::id());
            } elseif ($chave === 'novos') {
                 $query->whereNull('tecnico_atribuido_id');
            }

             if (in_array($chave, ['novos', 'meus', 'aguardando_atendimento'])) {
                $query->orderByRaw("FIELD(prioridade, 'Urgente', 'Alta', 'Média', 'Baixa')")
                      ->orderBy('created_at', 'asc');
            } elseif ($chave === 'meus_resolvidos' || $chave === 'fechados') {
                 // ORDENAÇÃO PARA FECHADOS: mais recentes primeiro
                 $query->latest('data_fechamento')->latest('data_resolucao'); 
            } else {
                $query->latest('updated_at');
            }

            $chamadosPorAba[$chave] = $query->get();
        }

        $queryTodos = clone $queryBase;
        
        $queryTodos->orderByRaw("FIELD(prioridade, 'Urgente', 'Alta', 'Média', 'Baixa')")
                   ->orderBy('created_at', 'asc');
        $todosChamados = $queryTodos->paginate(20)->withQueryString();

        // Dados para os Selects
        $clientes = \App\Models\Cliente::orderBy('nome')->pluck('nome', 'id');
        $prioridades = ['Urgente', 'Alta', 'Média', 'Baixa'];
        $tecnicos = \App\Models\User::where('empresa_id', Auth::user()->empresa_id)->orderBy('name')->pluck('name', 'id');
        $statusOptions = ['Aberto', 'Em Atendimento', 'Aguardando Cliente', 'Aguardando Atendimento', 'Resolvido Online', 'Convertido em OS', 'Fechado'];
        
        // Se um cliente for selecionado, carregamos os equipamentos para a view
        $equipamentosDoCliente = collect();
        if ($request->filled('cliente_id')) {
            $equipamentosDoCliente = ClienteEquipamento::where('cliente_id', $request->cliente_id)
                                                    ->orderBy('descricao')
                                                    ->get(['id', 'descricao', 'numero_serie']);
        }


        return view('admin.chamados.index', compact(
            'chamadosPorAba',
            'todosChamados',
            'clientes',
            'prioridades',
            'tecnicos', 
            'statusOptions', 
            'equipamentosDoCliente',
            'request'
        ));
    }

    public function create()
    {
        // Busca clientes para o dropdown - CORREÇÃO DE ERRO: Usando 'nome' do cliente ao invés de 'nome_fantasia'
        $clientes = Cliente::orderBy('nome')->get(['id', 'nome']);
        // Busca equipamentos (opcional, pode ser selecionado após escolher o cliente via JS)
        $equipamentos = collect(); // Começa vazio
        $prioridades = ['Baixa', 'Média', 'Alta', 'Urgente'];

        return view('admin.chamados.create', compact('clientes', 'equipamentos', 'prioridades'));
    }

    public function show(SuporteChamado $chamado)
    {
        $chamado->load(
            'cliente', 'equipamento', 'tecnico', 'ordemServico',
            'mensagens.user', 'mensagens.cliente', 'mensagens.anexos', 'anexos'
        );
        // CORREÇÃO DE ERRO: Removendo filtro 'user_tipo' que não existe na tabela users
        // Busca todos os usuários da empresa do chamado (assumindo que todos podem ser técnicos)
        $tecnicosDisponiveis = User::where('empresa_id', $chamado->empresa_id)->orderBy('name')->get(); 
        
        // ==========================================================
        // |||||||||||||||||||| LÓGICA DA BASE DE CONHECIMENTO ||||||||||||||||||||
        // ==========================================================
        $historicoSolucoes = SuporteChamado::select('id', 'protocolo', 'solucao_aplicada', 'updated_at')
            ->whereIn('status', ['Resolvido Online', 'Fechado', 'Convertido em OS']) // Apenas chamados finalizados
            ->whereNotNull('solucao_aplicada') // Que tenham uma solução registrada
            ->where('id', '!=', $chamado->id) // Exclui o chamado atual
            ->where(function($query) use ($chamado) {
                // Prioridade 1: Mesmo equipamento
                if ($chamado->cliente_equipamento_id) {
                    $query->where('cliente_equipamento_id', $chamado->cliente_equipamento_id);
                } else {
                    // Prioridade 2: Mesmo cliente, se não houver equipamento
                    $query->where('cliente_id', $chamado->cliente_id);
                }
            })
            ->latest('updated_at')
            ->take(5) // Limita aos 5 últimos
            ->get();


        return view('admin.chamados.show', compact('chamado', 'tecnicosDisponiveis', 'historicoSolucoes')); // Passa técnicos e a KB
    }

    public function atribuir(SuporteChamado $chamado)
    {
         // REGRA DE NEGÓCIO: BLOQUEAR MODIFICAÇÃO SE FINALIZADO
        if (in_array($chamado->status, ['Fechado', 'Convertido em OS', 'Resolvido Online'])) {
             return redirect()->back()->with('error', 'Não é possível atribuir a mim um chamado que já está finalizado.');
        }

        if ($chamado->tecnico_atribuido_id) {
             return redirect()->back()->with('error', 'Este chamado já está atribuído a outro técnico.');
        }
        
        $user = Auth::user();
        $chamado->update(['tecnico_atribuido_id' => $user->id, 'status' => 'Em Atendimento']);
        $chamado->mensagens()->create(['user_id' => $user->id, 'tipo' => 'Log', 'mensagem' => 'Chamado atribuído a: ' . $user->name]);
        return redirect()->back()->with('success', 'Chamado atribuído a você!');
    }

    public function responder(Request $request, SuporteChamado $chamado)
    {
        // REGRA DE NEGÓCIO: BLOQUEAR MODIFICAÇÃO SE FINALIZADO
        if (in_array($chamado->status, ['Fechado', 'Convertido em OS'])) { // Verifica se fechado ou convertido
            return redirect()->back()->with('error', 'Não é possível responder a um chamado fechado ou convertido em OS.');
        }
        $validated = $request->validate([ 'mensagem' => 'required|string|min:5', 'interno' => 'nullable|boolean', 'resp_anexos' => 'nullable|array', 'resp_anexos.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120']);
        $user = Auth::user();
        $interno = $request->has('interno');
        $mensagem = $chamado->mensagens()->create(['user_id' => $user->id, 'mensagem' => $validated['mensagem'], 'tipo' => 'Comentário', 'interno' => $interno ]);
        if ($request->hasFile('resp_anexos')) {
            foreach ($request->file('resp_anexos') as $file) {
                // Certifique-se de que a rota de armazenamento está correta para onde o Laravel resolve
                $caminho = $file->store('public/chamados/' . $chamado->id . '/' . $mensagem->id);
                $mensagem->anexos()->create(['chamado_id' => $chamado->id, 'caminho_arquivo' => $caminho, 'nome_original' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ]);
            }
        }
        // Se a resposta não for interna e o status não for finalizado, muda o status
        if (!$interno && !in_array($chamado->status, ['Resolvido Online', 'Fechado', 'Convertido em OS'])) {
            $chamado->update(['status' => 'Aguardando Cliente']);
        }
        return redirect()->back()->with('success', $interno ? 'Nota interna adicionada!' : 'Resposta enviada ao cliente!');
    }

    public function reatribuir(Request $request, SuporteChamado $chamado)
    {
        // REGRA DE NEGÓCIO: BLOQUEAR MODIFICAÇÃO SE FINALIZADO
        if (in_array($chamado->status, ['Fechado', 'Resolvido Online', 'Convertido em OS'])) { 
             return redirect()->back()->with('error', 'Este chamado não pode ser reatribuído no momento.');
        }
        $validated = $request->validate(['novo_tecnico_id' => ['required', 'exists:users,id', Rule::notIn([$chamado->tecnico_atribuido_id])]]);
        $userAtual = Auth::user();
        $tecnicoAntigo = $chamado->tecnico;
        $novoTecnico = User::find($validated['novo_tecnico_id']);
        $updateData = ['tecnico_atribuido_id' => $novoTecnico->id];
        // Se estiver aguardando o cliente, volta para 'Em Atendimento' ou 'Aguardando Atendimento'
        if ($chamado->status == 'Aguardando Cliente') { $updateData['status'] = 'Em Atendimento'; }
        // Se for de "resolvido" para reatribuição, volta para "Em Atendimento"
        if ($chamado->status == 'Resolvido Online') { $updateData['status'] = 'Em Atendimento'; }
        
        $chamado->update($updateData);
        $chamado->mensagens()->create(['user_id' => $userAtual->id, 'tipo' => 'Log', 'mensagem' => "Chamado reatribuído de '{$tecnicoAntigo->name}' para '{$novoTecnico->name}' por {$userAtual->name}." ]);
        return redirect()->back()->with('success', "Chamado reatribuído para {$novoTecnico->name} com sucesso!");
    }

    public function salvarSolucao(Request $request, SuporteChamado $chamado)
    {
         // REGRA DE NEGÓCIO: BLOQUEAR MODIFICAÇÃO SE FINALIZADO
         if (in_array($chamado->status, ['Fechado', 'Convertido em OS'])) { 
             return redirect()->back()->with('error', 'Não é possível alterar a solução de um chamado fechado ou convertido em OS.');
         }
        $validated = $request->validate(['solucao_aplicada' => 'nullable|string|max:65000']);
        $chamado->update(['solucao_aplicada' => $validated['solucao_aplicada']]);
        return redirect()->back()->with('success', 'Solução do chamado salva com sucesso!');
    }

    public function mudarStatus(Request $request, SuporteChamado $chamado)
    {
        // REGRA DE NEGÓCIO: BLOQUEAR MODIFICAÇÃO SE FECHADO (Exceto para reabertura por supervisor, que não implementamos)
        if ($chamado->status == 'Fechado' && $request->status !== 'Aberto') { 
             return redirect()->back()->with('error', 'Um chamado fechado não pode ter seu status alterado.');
        }
        
        $statusOptions = ['Aberto', 'Em Atendimento', 'Aguardando Cliente', 'Aguardando Atendimento', 'Resolvido Online', 'Fechado'];
        $validated = $request->validate(['status' => ['required', Rule::in($statusOptions)]]);
        $novoStatus = $validated['status'];
        $statusAntigo = $chamado->status;
        
        if ($novoStatus != $statusAntigo) {
            $updateData = ['status' => $novoStatus];
            
            // Adiciona data de resolução/fechamento
            if ($novoStatus == 'Resolvido Online' && !$chamado->data_resolucao) { 
                $updateData['data_resolucao'] = Carbon::now(); 
            }
            if ($novoStatus == 'Fechado' && !$chamado->data_fechamento) {
                $updateData['data_fechamento'] = Carbon::now();
                if (!$chamado->data_resolucao) $updateData['data_resolucao'] = Carbon::now();
            }
            
            // Se reabrir (status não finalizado), remove a data de fechamento
            if (!in_array($novoStatus, ['Resolvido Online', 'Fechado'])) {
                 $updateData['data_fechamento'] = null; 
            }

            $chamado->update($updateData);
            $chamado->mensagens()->create(['user_id' => Auth::id(), 'tipo' => 'Log', 'mensagem' => "Status alterado de '{$statusAntigo}' para '{$novoStatus}' por " . Auth::user()->name . "." ]);
            return redirect()->back()->with('success', 'Status do chamado atualizado!');
        }
        return redirect()->back();
    }

    public function mudarPrioridade(Request $request, SuporteChamado $chamado)
    {
         // REGRA DE NEGÓCIO: BLOQUEAR MODIFICAÇÃO SE FINALIZADO
         if (in_array($chamado->status, ['Fechado', 'Convertido em OS'])) { 
             return redirect()->back()->with('error', 'Não é possível alterar a prioridade de um chamado fechado ou convertido em OS.');
         }
        $prioridadeOptions = ['Baixa', 'Média', 'Alta', 'Urgente'];
        $validated = $request->validate(['prioridade' => ['required', Rule::in($prioridadeOptions)]]);
        $novaPrioridade = $validated['prioridade'];
        $prioridadeAntiga = $chamado->prioridade;
        if ($novaPrioridade != $prioridadeAntiga) {
            $chamado->update(['prioridade' => $novaPrioridade]);
            $chamado->mensagens()->create(['user_id' => Auth::id(), 'tipo' => 'Log', 'mensagem' => "Prioridade alterada de '{$prioridadeAntiga}' para '{$novaPrioridade}' por " . Auth::user()->name . "." ]);
             return redirect()->back()->with('success', 'Prioridade do chamado atualizada!');
        }
         return redirect()->back();
    }

    public function store(Request $request)
    {
        $adminLogado = Auth::user();

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id', // Admin DEVE escolher o cliente
            'titulo' => 'required|string|max:255',
            'prioridade' => 'required|string|in:Baixa,Média,Alta,Urgente',
            'descricao_problema' => 'required|string|min:10',
            'cliente_equipamento_id' => 'nullable|exists:cliente_equipamentos,id',
            // Anexos são opcionais aqui também
            'anexos' => 'nullable|array',
            'anexos.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120'
        ]);

        // Não precisa buscar o cliente, o ID já está validado.

        $chamado = SuporteChamado::create([
            'empresa_id' => $adminLogado->empresa_id, // Pega empresa do admin logado
            'cliente_id' => $validated['cliente_id'],
            'titulo' => $validated['titulo'],
            'prioridade' => $validated['prioridade'],
            'descricao_problema' => $validated['descricao_problema'],
            'cliente_equipamento_id' => $validated['cliente_equipamento_id'],
            'status' => 'Aberto', // Status inicial
        ]);

        // Log de criação pelo Admin
        $chamado->mensagens()->create([
            'user_id' => $adminLogado->id,
            'tipo' => 'Log',
            'mensagem' => "Chamado criado por {$adminLogado->name} em nome do cliente.",
        ]);

        // Lógica de upload de anexos (igual à do portal)
        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $file) {
                $caminho = $file->store('public/chamados/' . $chamado->id);
                // A lógica completa de criação do anexo deve ser incluída aqui
                // Ex: $chamado->anexos()->create(['caminho_arquivo' => $caminho, 'nome_original' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ]);
            }
        }

        // Redireciona para a tela de atendimento do novo chamado
        return redirect()->route('admin.chamados.show', $chamado)
                         ->with('success', 'Novo chamado aberto com sucesso!');
    }

    public function equipamentosPorCliente(Request $request)
    {
        // 1. Usa o método input() para obter o ID de forma segura
        $clienteId = $request->input('cliente_id');
        
        // 2. Verifica se o cliente ID é válido ou retorna array vazio
        if (empty($clienteId) || !Cliente::where('id', $clienteId)->exists()) {
             // Retorna um array vazio se o cliente não for encontrado ou não for enviado
             return response()->json([]);
        }

        // 3. Busca os equipamentos vinculados ao cliente
        $equipamentos = ClienteEquipamento::where('cliente_id', $clienteId)
            ->select('id', 'descricao', 'numero_serie')
            ->get();
        
        // 4. Formata e retorna o JSON
        $response = $equipamentos->map(function ($equipamento) {
            $texto = $equipamento->descricao;
            if ($equipamento->numero_serie) {
                $texto .= ' (SN: ' . $equipamento->numero_serie . ')';
            }
            return [
                'id' => $equipamento->id,
                'texto' => $texto
            ];
        });

        return response()->json($response);
    }

    public function converterOS(SuporteChamado $chamado)
    {
         // REGRA DE NEGÓCIO: BLOQUEAR CONVERSÃO SE FINALIZADO
         if (in_array($chamado->status, ['Fechado', 'Resolvido Online', 'Convertido em OS'])) { 
             return redirect()->back()->with('error', 'Este chamado não pode ser convertido em OS, pois já está finalizado ou já foi convertido.');
         }
        $tecnico = Auth::user();
        if ($chamado->ordem_servico_id) { 
            return redirect()->route('ordens-servico.edit', $chamado->ordem_servico_id)->with('error', 'Este chamado já foi convertido na OS #' . $chamado->ordem_servico_id); 
        }
        
        $dadosOS = [
            'cliente_id' => $chamado->cliente_id,
            'tecnico_id' => $chamado->tecnico_atribuido_id ?? $tecnico->id,
            'status' => 'Aberta',
            'defeito_relatado' => "[Origem: Chamado #{$chamado->protocolo}]\n" . $chamado->descricao_problema,
            'cliente_equipamento_id' => $chamado->cliente_equipamento_id,
            'origem_chamado' => "Originada do Chamado #{$chamado->protocolo}.",
            'suporte_chamado_id' => $chamado->id, // ADICIONADO PARA O SERVIÇO
        ];
        
        // CUIDADO: Este serviço (osService) deve estar injetado corretamente.
        $ordemServico = $this->osService->criarOS($dadosOS, $tecnico); 
        
        $chamado->update(['ordem_servico_id' => $ordemServico->id, 'status' => 'Convertido em OS' ]);
        $chamado->mensagens()->create(['user_id' => $tecnico->id, 'tipo' => 'Log', 'mensagem' => 'Chamado convertido na Ordem de Serviço #' . $ordemServico->id ]);
        return redirect()->route('ordens-servico.edit', $ordemServico->id)->with('success', 'Chamado convertido com sucesso! A OS #' . $ordemServico->id . ' foi criada.');
    }

} // Fim da classe