<x-portal-layout>
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Chamado: {{ $chamado->protocolo }}</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400">{{ $chamado->titulo }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500 dark:text-gray-300">Status:
                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                    @switch($chamado->status)
                        @case('Aberto') @case('Aguardando Atendimento')
                            bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                        @case('Em Atendimento') @case('Aguardando Cliente')
                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 @break
                        @case('Resolvido Online') @case('Convertido em OS')
                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                        @default
                            bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                    @endswitch
                ">
                    {{ $chamado->status }}
                </span>
            </p>
             <p class="text-sm text-gray-500 dark:text-gray-300">Prioridade: {{ $chamado->prioridade }}</p>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- ||||||||||||||||| BLOCO DE EQUIPAMENTO ADICIONADO ||||||||||||||||| --}}
    {{-- ========================================================== --}}
    @if ($chamado->equipamento)
    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border dark:border-gray-600">
        <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200 mb-2">Equipamento Relacionado</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            <strong>Descrição:</strong> {{ $chamado->equipamento->descricao }} <br>
            @if ($chamado->equipamento->marca)
                <strong>Marca:</strong> {{ $chamado->equipamento->marca }} <br>
            @endif
            @if ($chamado->equipamento->modelo)
                <strong>Modelo:</strong> {{ $chamado->equipamento->modelo }} <br>
            @endif
            @if ($chamado->equipamento->numero_serie)
                <strong>Nº de Série:</strong> {{ $chamado->equipamento->numero_serie }}
            @endif
        </p>
    </div>
    @endif
    {{-- ================= FIM DO BLOCO DE EQUIPAMENTO ================= --}}


    {{-- TIMELINE (Alterada para usar Tailwind e ficar mais moderna) --}}
    <div class="space-y-4">
        {{-- Problema Original --}}
        <div class="flex items-start gap-3">
            {{-- Avatar Simples --}}
            <span class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-500 text-white font-medium text-xs">
                {{ substr($chamado->cliente->nome ?? 'C', 0, 1) }}
            </span>
            <div class="flex-1 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border dark:border-gray-700">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">{{ $chamado->cliente->nome ?? 'Cliente' }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $chamado->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $chamado->descricao_problema }}</p>
                {{-- (Adicionar aqui a listagem de anexos iniciais, se houver) --}}
            </div>
        </div>

        {{-- Mensagens da Timeline --}}
        @foreach ($chamado->mensagens as $msg)

{{-- ========================================================== --}}
{{-- ||||||||||||||||||| CONDIÇÃO ADICIONADA AQUI ||||||||||||||||| --}}
{{-- ========================================================== --}}
{{-- Só exibe a mensagem SE NÃO FOR INTERNA --}}
@if(!$msg->interno) 

    @if ($msg->tipo === 'Log')
        <div class="text-center my-2">
            <span class="text-xs text-gray-500 dark:text-gray-400 italic bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $msg->mensagem }} <small>({{ $msg->created_at->format('d/m H:i') }})</small></span>
        </div>
    @elseif ($msg->user_id) {{-- Resposta do Técnico (NÃO INTERNA) --}}
        <div class="flex items-start gap-3 justify-end">
             <div class="order-1 flex-1 bg-blue-50 dark:bg-blue-900/50 p-4 rounded-lg shadow-sm border border-blue-200 dark:border-blue-800 max-w-xl">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-semibold text-sm text-blue-900 dark:text-blue-200">{{ $msg->user->name ?? 'Suporte' }}</span>
                    <span class="text-xs text-blue-700 dark:text-blue-400">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="text-sm text-blue-800 dark:text-blue-300 whitespace-pre-wrap">{{ $msg->mensagem }}</p>
                {{-- Anexos da Mensagem --}}
                @if($msg->anexos->count() > 0)
                    <div class="mt-2 pt-2 border-t border-blue-300 dark:border-blue-700">
                        <p class="text-xs font-medium text-blue-700 dark:text-blue-400 mb-1">Anexos:</p>
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

@endif {{-- Fim do @if(!$msg->interno) --}}
{{-- ================= FIM DA CONDIÇÃO ================= --}}

@endforeach
    </div>

    {{-- Formulário de Resposta (Modernizado) --}}
    @if (!in_array($chamado->status, ['Resolvido Online', 'Convertido em OS', 'Fechado']))
        <div class="mt-6 pt-6 border-t dark:border-gray-700">
            <form action="{{ route('portal.chamados.responder', $chamado) }}" method="POST">
                @csrf
                <x-input-label for="mensagem" value="Sua Resposta" class="mb-2"/>
                <textarea name="mensagem" id="mensagem" rows="5" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Escreva sua resposta..." required></textarea>
                <x-input-error :messages="$errors->get('mensagem')" class="mt-2" />
                {{-- (Adicionar campo de anexo para resposta aqui, se desejar) --}}
                <div class="mt-4 flex justify-end">
                    <x-primary-button>
                        Enviar Resposta
                    </x-primary-button>
                </div>
            </form>
        </div>
    @else
        <p class="mt-6 pt-6 border-t dark:border-gray-700 text-center text-sm text-gray-500 dark:text-gray-400">Este chamado está encerrado.</p>
    @endif
</x-portal-layout>