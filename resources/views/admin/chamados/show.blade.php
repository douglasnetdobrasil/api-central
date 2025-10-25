<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Atender Chamado #{{ $chamado->protocolo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- ============================================= --}}
            {{-- ||||||||||||| COLUNA ESQUERDA (DETALHES E AÇÕES) ||||||||||||| --}}
            {{-- ============================================= --}}
            <div class="md:col-span-1 space-y-6">

                {{-- BLOCO DE DETALHES DO CHAMADO --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Detalhes do Chamado</h3>
                    <div class="space-y-3 text-sm">
                        <p><strong class="text-gray-500 dark:text-gray-400 w-24 inline-block">Protocolo:</strong> {{ $chamado->protocolo }}</p>
                        <p><strong class="text-gray-500 dark:text-gray-400 w-24 inline-block">Status:</strong>
                            {{-- CORES DO STATUS --}}
                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                @switch($chamado->status)
                                    @case('Aberto') @case('Aguardando Atendimento') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                                    @case('Em Atendimento') @case('Aguardando Cliente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 @break
                                    @case('Resolvido Online') @case('Convertido em OS') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                    @default bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @endswitch
                            ">
                                {{ $chamado->status }}
                            </span>
                        </p>
                        <p><strong class="text-gray-500 dark:text-gray-400 w-24 inline-block">Prioridade:</strong> {{ $chamado->prioridade }}</p>
                        <p><strong class="text-gray-500 dark:text-gray-400 w-24 inline-block">Aberto em:</strong> {{ $chamado->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong class="text-gray-500 dark:text-gray-400 w-24 inline-block">Últ. Update:</strong> {{ $chamado->updated_at->diffForHumans() }}</p>
                        <p><strong class="text-gray-500 dark:text-gray-400 w-24 inline-block">Técnico:</strong> {{ $chamado->tecnico->name ?? 'Não atribuído' }}</p>
                    </div>
                </div>

                {{-- BLOCO DE DETALHES DO CLIENTE --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                     <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Cliente</h3>
                     <div class="space-y-3 text-sm">
                        <p><strong class="text-gray-500 dark:text-gray-400 w-20 inline-block">Nome:</strong>
                            {{-- Link para o cliente (ajuste a rota se necessário) --}}
                            <a href="{{ route('clientes.edit', $chamado->cliente_id) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $chamado->cliente->nome ?? 'N/A' }}
                            </a>
                        </p>
                        <p><strong class="text-gray-500 dark:text-gray-400 w-20 inline-block">Telefone:</strong> {{ $chamado->cliente->telefone ?? '-' }}</p>
                        <p><strong class="text-gray-500 dark:text-gray-400 w-20 inline-block">Email:</strong> {{ $chamado->cliente->email ?? '-' }}</p>
                    </div>
                </div>

                {{-- BLOCO DE DETALHES DO EQUIPAMENTO --}}
                @if ($chamado->equipamento)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Equipamento</h3>
                        <div class="space-y-3 text-sm">
                            <p><strong class="text-gray-500 dark:text-gray-400 w-20 inline-block">Descrição:</strong> {{ $chamado->equipamento->descricao }}</p>
                            @if($chamado->equipamento->marca)<p><strong class="text-gray-500 dark:text-gray-400 w-20 inline-block">Marca:</strong> {{ $chamado->equipamento->marca }}</p>@endif
                            @if($chamado->equipamento->modelo)<p><strong class="text-gray-500 dark:text-gray-400 w-20 inline-block">Modelo:</strong> {{ $chamado->equipamento->modelo }}</p>@endif
                            @if($chamado->equipamento->numero_serie)<p><strong class="text-gray-500 dark:text-gray-400 w-20 inline-block">Nº Série:</strong> {{ $chamado->equipamento->numero_serie }}</p>@endif
                             {{-- Link para editar equipamento (ajuste a rota) --}}
                             <a href="{{ route('cliente-equipamentos.edit', $chamado->cliente_equipamento_id) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs mt-2 inline-block">
                                Ver/Editar Equipamento
                            </a>
                        </div>
                    </div>
                @endif

                {{-- BLOCO DE SOLUÇÃO APLICADA --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Solução Aplicada</h3>
                    <form action="{{ route('admin.chamados.salvarSolucao', $chamado) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <textarea name="solucao_aplicada" rows="6" 
                                  class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" 
                                  placeholder="Descreva a solução aplicada para referência futura..."
                                  {{ in_array($chamado->status, ['Fechado', 'Convertido em OS']) ? 'disabled' : '' }} 
                                  >{{ old('solucao_aplicada', $chamado->solucao_aplicada) }}</textarea>
                        <x-input-error :messages="$errors->get('solucao_aplicada')" class="mt-2" />
                        
                        @if (!in_array($chamado->status, ['Fechado', 'Convertido em OS']))
                        <div class="mt-4 flex justify-end">
                            <x-primary-button>Salvar Solução</x-primary-button>
                        </div>
                        @endif
                    </form>
                </div>

                {{-- BLOCO DE AÇÕES RÁPIDAS --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Ações</h3>
                    <div class="space-y-3">
                        
                         {{-- ** INÍCIO: BLOCO DE CONTROLE PARA CHAMADOS ATIVOS ** --}}
                        @if (!in_array($chamado->status, ['Fechado', 'Convertido em OS']))
                            
                            {{-- Atribuir a mim --}}
                            @if(!$chamado->tecnico_atribuido_id && $chamado->status == 'Aberto')
                                <form action="{{ route('admin.chamados.atribuir', $chamado) }}" method="POST">
                                    @csrf
                                    <x-secondary-button type="submit" class="w-full justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        Atribuir a mim
                                    </x-secondary-button>
                                </form>
                            @endif

                            {{-- Mudar Status (Formulário) --}}
                            <form action="{{ route('admin.chamados.mudarStatus', $chamado) }}" method="POST"> 
                                @csrf
                                @method('PATCH')
                                <div class="flex items-center gap-2">
                                    <select name="status" class="flex-grow block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        @php $statusOptions = ['Aberto', 'Em Atendimento', 'Aguardando Cliente', 'Aguardando Atendimento', 'Resolvido Online', 'Fechado']; @endphp
                                        @foreach($statusOptions as $status)
                                            <option value="{{ $status }}" {{ $chamado->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <x-secondary-button type="submit" class="flex-shrink-0" title="Atualizar Status">OK</x-secondary-button>
                                </div>
                            </form>

                            {{-- Mudar Prioridade (Formulário) --}}
                            <form action="{{ route('admin.chamados.mudarPrioridade', $chamado) }}" method="POST"> 
                                 @csrf
                                 @method('PATCH')
                                <div class="flex items-center gap-2">
                                    <select name="prioridade" class="flex-grow block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                         @php $prioridadeOptions = ['Baixa', 'Média', 'Alta', 'Urgente']; @endphp
                                        @foreach($prioridadeOptions as $p)
                                            <option value="{{ $p }}" {{ $chamado->prioridade == $p ? 'selected' : '' }}>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                    <x-secondary-button type="submit" class="flex-shrink-0" title="Atualizar Prioridade">OK</x-secondary-button>
                                </div>
                            </form>

                            {{-- Reatribuir --}}
                            @if($chamado->tecnico_atribuido_id && !in_array($chamado->status, ['Fechado', 'Resolvido Online', 'Convertido em OS']))
                                <form action="{{ route('admin.chamados.reatribuir', $chamado) }}" method="POST"> 
                                    @csrf
                                    @method('PATCH')
                                    <label for="novo_tecnico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">Reatribuir para:</label>
                                    <div class="flex items-center gap-2">
                                        <select name="novo_tecnico_id" id="novo_tecnico_id" class="flex-grow block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                            <option value="">Selecione...</option>
                                            {{-- $tecnicosDisponiveis vem do Controller --}}
                                            @foreach($tecnicosDisponiveis ?? [] as $tec)
                                                {{-- Não permite reatribuir para si mesmo --}}
                                                @if($tec->id !== $chamado->tecnico_atribuido_id)
                                                    <option value="{{ $tec->id }}">{{ $tec->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <x-secondary-button type="submit" class="flex-shrink-0" title="Reatribuir Chamado">OK</x-secondary-button>
                                    </div>
                                    <x-input-error :messages="$errors->get('novo_tecnico_id')" class="mt-2" />
                                </form>
                            @endif

                            {{-- Converter em OS --}}
                            @if(!$chamado->ordem_servico_id && !in_array($chamado->status, ['Fechado', 'Resolvido Online', 'Convertido em OS']))
                                <form action="{{ route('admin.chamados.converterOS', $chamado) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja converter este chamado em uma Ordem de Serviço?')">
                                    @csrf
                                    <x-primary-button type="submit" class="w-full justify-center bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:ring-green-500">
                                       <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                        Converter em OS
                                    </x-primary-button>
                                </form>
                            @endif
                        
                        @endif {{-- FIM do @if de verificação de status Fechado/Convertido --}}

                        {{-- Ver OS (Sempre visível se já convertido) --}}
                        @if($chamado->ordem_servico_id)
                             <a href="{{ route('ordens-servico.edit', $chamado->ordem_servico_id) }}" target="_blank"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Ver OS #{{ $chamado->ordem_servico_id }}
                            </a>
                        @endif

                         {{-- Mensagem de Chamado Finalizado --}}
                        @if (in_array($chamado->status, ['Fechado', 'Convertido em OS']))
                            <div class="p-3 text-sm text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/50 rounded-lg text-center font-medium border border-red-300 dark:border-red-700">
                                Chamado Finalizado ({{ $chamado->status }}). Não é possível realizar modificações. Se necessário, abra um novo chamado.
                            </div>
                        @endif
                        
                    </div>
                </div>

                {{-- ====================================================== --}}
                {{-- |||||||||||||| BLOCO DA BASE DE CONHECIMENTO (KB) |||||||||||||| --}}
                {{-- ====================================================== --}}
                @if ($historicoSolucoes->count())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-purple-700 dark:text-purple-400 mb-4 border-b pb-2 dark:border-gray-700">
                        Base de Conhecimento (KB)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Últimas soluções aplicadas para este {{ $chamado->cliente_equipamento_id ? 'equipamento' : 'cliente' }}.
                    </p>
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                        @foreach ($historicoSolucoes as $solucao)
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-md border border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between items-start mb-2">
                                <a href="{{ route('admin.chamados.show', $solucao->id) }}" target="_blank" class="font-semibold text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Chamado #{{ $solucao->protocolo }}
                                </a>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $solucao->updated_at->format('d/m/Y') }}</span>
                            </div>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap line-clamp-4">
                                {{ $solucao->solucao_aplicada }}
                            </p>
                             <a href="{{ route('admin.chamados.show', $solucao->id) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-1 inline-block">
                                Ver Chamado Completo
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                 {{-- Mensagem se não houver soluções --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">
                        Base de Conhecimento (KB)
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Nenhuma solução anterior registrada para este {{ $chamado->cliente_equipamento_id ? 'equipamento' : 'cliente' }} que possa ser usada como Base de Conhecimento.
                    </p>
                </div>
                @endif


            </div>

            {{-- ============================================= --}}
            {{-- ||||||||||||| COLUNA DIREITA (TIMELINE E RESPOSTA) ||||||||||||| --}}
            {{-- ============================================= --}}
            <div class="md:col-span-2 space-y-6">

                {{-- BLOCO DA TIMELINE --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">Histórico do Atendimento</h3>
                    <div class="space-y-4">
                        {{-- Problema Original --}}
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-500 text-white font-medium text-xs" title="{{ $chamado->cliente->nome ?? 'Cliente' }}">
                                {{ substr($chamado->cliente->nome ?? 'C', 0, 1) }}
                            </span>
                            <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border dark:border-gray-600">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ $chamado->cliente->nome ?? 'Cliente' }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(Problema Original)</span></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $chamado->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $chamado->descricao_problema }}</p>
                                {{-- Anexos Originais --}}
                                @if($chamado->anexos->whereNull('mensagem_id')->count() > 0)
                                    <div class="mt-2 pt-2 border-t dark:border-gray-600">
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Anexos:</p>
                                        <ul class="list-none space-y-1">
                                            @foreach($chamado->anexos->whereNull('mensagem_id') as $anexo)
                                            <li>
                                                <a href="{{ Storage::url($anexo->caminho_arquivo) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                                    {{ $anexo->nome_original }}
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Mensagens da Timeline --}}
                        @foreach ($chamado->mensagens as $msg)
                            @if ($msg->tipo === 'Log')
                                <div class="text-center my-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $msg->mensagem }} <small>({{ $msg->created_at->format('d/m H:i') }})</small></span>
                                </div>
                            @elseif ($msg->user_id) {{-- Resposta do Técnico --}}
                                <div class="flex items-start gap-3 justify-end">
                                    <div class="order-1 flex-1 p-4 rounded-lg shadow-sm border max-w-xl
                                        {{ $msg->interno ? 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800' : 'bg-blue-50 dark:bg-blue-900/50 border-blue-200 dark:border-blue-800' }}">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-semibold text-sm {{ $msg->interno ? 'text-yellow-900 dark:text-yellow-200' : 'text-blue-900 dark:text-blue-200' }}">
                                                {{ $msg->user->name ?? 'Suporte' }}
                                                @if($msg->interno) <span class="text-xs font-normal">(Nota Interna)</span> @endif
                                            </span>
                                            <span class="text-xs {{ $msg->interno ? 'text-yellow-700 dark:text-yellow-400' : 'text-blue-700 dark:text-blue-400' }}">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="text-sm {{ $msg->interno ? 'text-yellow-800 dark:text-yellow-300' : 'text-blue-800 dark:text-blue-300' }} whitespace-pre-wrap">{{ $msg->mensagem }}</p>
                                        {{-- Anexos da Mensagem --}}
                                        @if($msg->anexos->count() > 0)
                                            <div class="mt-2 pt-2 border-t {{ $msg->interno ? 'border-yellow-300 dark:border-yellow-700' : 'border-blue-300 dark:border-blue-700' }}">
                                                <p class="text-xs font-medium {{ $msg->interno ? 'text-yellow-700 dark:text-yellow-400' : 'text-blue-700 dark:text-blue-400' }} mb-1">Anexos:</p>
                                                <ul class="list-none space-y-1">
                                                    @foreach($msg->anexos as $anexo)
                                                    <li><a href="{{ Storage::url($anexo->caminho_arquivo) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ $anexo->nome_original }}</a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="order-2 flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-600 text-white font-medium text-xs" title="{{ $msg->user->name ?? 'Suporte' }}">
                                        {{ substr($msg->user->name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                            @else {{-- Resposta do Cliente --}}
                                <div class="flex items-start gap-3">
                                    <span class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-500 text-white font-medium text-xs" title="{{ $msg->cliente->nome ?? 'Cliente' }}">
                                        {{ substr($msg->cliente->nome ?? 'C', 0, 1) }}
                                    </span>
                                    <div class="flex-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border dark:border-gray-700 max-w-xl">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ $msg->cliente->nome ?? 'Cliente' }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $msg->mensagem }}</p>
                                        {{-- Anexos da Mensagem --}}
                                        @if($msg->anexos->count() > 0)
                                            <div class="mt-2 pt-2 border-t dark:border-gray-600">
                                                 <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Anexos:</p>
                                                <ul class="list-none space-y-1">
                                                    @foreach($msg->anexos as $anexo)
                                                    <li><a href="{{ Storage::url($anexo->caminho_arquivo) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">{{ $anexo->nome_original }}</a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- BLOCO DE RESPOSTA DO TÉCNICO --}}
                 @if (!in_array($chamado->status, ['Fechado', 'Resolvido Online', 'Convertido em OS']))
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                         <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Responder Chamado</h3>
                        <form action="{{ route('admin.chamados.responder', $chamado) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <textarea name="mensagem" rows="5" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Escreva sua resposta para o cliente ou uma nota interna..." required></textarea>
                            <x-input-error :messages="$errors->get('mensagem')" class="mt-2" />

                            {{-- Anexos da Resposta --}}
                             <div class="mt-4">
                                <label for="resp_anexos" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Anexar arquivos</label>
                                <input type="file" name="resp_anexos[]" id="resp_anexos" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300"/>
                                <x-input-error :messages="$errors->get('resp_anexos.*')" class="mt-2" />
                             </div>

                            <div class="mt-4 flex justify-between items-center">
                                <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <input type="checkbox" name="interno" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2">Salvar como Nota Interna (não visível ao cliente)</span>
                                </label>
                                <x-primary-button>
                                    Enviar Resposta
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                 @endif

            </div>

        </div>
    </div>
</x-app-layout>