{{-- resources/views/admin/chamados/partials/tabela-chamados.blade.php --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prio.</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Protocolo</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Título</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Técnico</th>
                {{-- REMOVIDA COLUNA "Aberto em" --}}
                {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aberto em</th> --}}
                {{-- ========================================================== --}}
                {{-- ||||||||||||||||||| COLUNA TEMPO DE ESPERA ADICIONADA ||||||||||||||||| --}}
                {{-- ========================================================== --}}
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aguardando Ação Há</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($chamados as $chamado)
                {{-- Destaque de fundo por prioridade --}}
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 {{ $chamado->prioridade == 'Urgente' ? 'bg-red-50 dark:bg-red-900/50' : ($chamado->prioridade == 'Alta' ? 'bg-yellow-50 dark:bg-yellow-900/50' : '') }}">
                    {{-- Prioridade --}}
                    <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-medium" title="Prioridade: {{ $chamado->prioridade }}">
                        @if($chamado->prioridade == 'Urgente') <span class="text-red-600 dark:text-red-400">!!</span>
                        @elseif($chamado->prioridade == 'Alta') <span class="text-yellow-600 dark:text-yellow-400">!</span>
                        @else <span class="text-gray-400">-</span> @endif
                    </td>
                    {{-- Protocolo --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $chamado->protocolo }}</td>
                    {{-- Cliente --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $chamado->cliente->nome ?? 'N/A' }}</td>
                    {{-- Título --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ Str::limit($chamado->titulo, 35) }}</td>
                    {{-- Status --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        {{-- ========================================================== --}}
                        {{-- |||||||||||||| INDICADOR DE NOVA RESPOSTA ADICIONADO |||||||||||||| --}}
                        {{-- ========================================================== --}}
                        @if ($chamado->status == 'Aguardando Atendimento')
                            <span class="mr-1.5 inline-flex items-center justify-center h-2 w-2 rounded-full bg-blue-500" title="Cliente respondeu"></span>
                        @endif
                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{-- ... (lógica @switch de cores do status - sem alteração) ... --}}
                            @switch($chamado->status)
                                @case('Aberto')
                                @case('Aguardando Atendimento')
                                    bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @break
                                @case('Em Atendimento')
                                @case('Aguardando Cliente')
                                    bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 @break
                                @case('Resolvido Online')
                                @case('Convertido em OS')
                                    bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 @break
                                @default
                                    bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @endswitch
                        ">
                            {{ $chamado->status }}
                        </span>
                    </td>
                    {{-- Técnico --}}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $chamado->tecnico->name ?? '-' }}</td>
                    {{-- REMOVIDA CÉLULA "Aberto em" --}}
                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $chamado->created_at->format('d/m/Y H:i') }}</td> --}}
                    {{-- ========================================================== --}}
                    {{-- ||||||||||||||||| CÉLULA TEMPO DE ESPERA ADICIONADA ||||||||||||||||| --}}
                    {{-- ========================================================== --}}
                    {{-- Calcula o tempo com base no 'created_at' se for novo, senão usa 'updated_at' --}}
                    @php
                        $tempoReferencia = ($chamado->status == 'Aberto' && !$chamado->tecnico_atribuido_id) ? $chamado->created_at : $chamado->updated_at;
                        $diff = $tempoReferencia->diffInHours(now());
                        $corTempo = $diff >= 48 ? 'text-red-600 dark:text-red-400 font-semibold' : ($diff >= 8 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400');
                    @endphp
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $corTempo }}">
                        {{ $tempoReferencia->diffForHumans() }}
                    </td>
                    {{-- Ações --}}
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.chamados.show', $chamado) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">
                            Atender
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    {{-- Ajustar colspan para 8 colunas --}}
                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                        Nenhum chamado encontrado nesta aba{{ $request->cliente_id || $request->prioridade ? ' para os filtros aplicados' : '' }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Adiciona uma legenda simples (opcional) --}}
<div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
    <span>Legenda Tempo:</span>
    <span class="text-gray-500 dark:text-gray-400">Menos de 8h</span> |
    <span class="text-yellow-600 dark:text-yellow-400">8h a 48h</span> |
    <span class="text-red-600 dark:text-red-400">Mais de 48h</span> |
    <span class="ml-4 inline-flex items-center">
        <span class="mr-1.5 inline-flex h-2 w-2 rounded-full bg-blue-500"></span> Cliente Respondeu
    </span>
</div>