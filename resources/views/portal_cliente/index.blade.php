<x-portal-layout>

    {{-- Notificação de Sucesso --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-900 dark:text-green-300 rounded-md" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- CABEÇALHO --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
            Meus Chamados
        </h2>
        <a href="{{ route('portal.chamados.create') }}"
           class="w-full sm:w-auto inline-flex justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Abrir Novo Chamado
        </a>
    </div>

    {{-- BARRA DE FILTROS --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <form action="{{ route('portal.dashboard') }}" method="GET" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Filtrar por Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="todos">Todos os Status</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" {{ $statusFiltro == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Filtrar
            </button>
        </form>
    </div>

    {{-- LISTAGEM DE CHAMADOS (COM RESPONSIVIDADE CORRIGIDA) --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

        {{-- Tabela (Visível em SM para cima, escondida abaixo) --}}
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 hidden sm:table">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        {{-- ========================================================== --}}
                        {{-- ||||||||||||||||||| COLUNA PRIORIDADE ADICIONADA ||||||||||||||||| --}}
                        {{-- ========================================================== --}}
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prio.</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Protocolo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Título</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Última Atualização</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($chamados as $chamado)
                        {{-- Adiciona uma classe de fundo se for Urgente/Alta --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 {{ $chamado->prioridade == 'Urgente' ? 'bg-red-50 dark:bg-red-900/50' : ($chamado->prioridade == 'Alta' ? 'bg-yellow-50 dark:bg-yellow-900/50' : '') }}">
                            {{-- ========================================================== --}}
                            {{-- ||||||||||||||||||| CÉLULA PRIORIDADE ADICIONADA ||||||||||||||||| --}}
                            {{-- ========================================================== --}}
                            <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-medium" title="Prioridade: {{ $chamado->prioridade }}">
                                @if($chamado->prioridade == 'Urgente')
                                    <span class="text-red-600 dark:text-red-400">!!</span>
                                @elseif($chamado->prioridade == 'Alta')
                                    <span class="text-yellow-600 dark:text-yellow-400">!</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $chamado->protocolo }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ Str::limit($chamado->titulo, 40) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{-- Span de Status (código existente) --}}
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full ...">
                                    {{ $chamado->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $chamado->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('portal.chamados.show', $chamado) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900">
                                    Ver Detalhes
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            {{-- Ajustar colspan por causa da nova coluna --}}
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Nenhum chamado encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        {{-- Cards (Visível APENAS ABAIXO de SM) --}}
        <div class="sm:hidden"> {{-- <-- 'sm:hidden' APLICADO AQUI --}}
            @forelse ($chamados as $chamado)
                <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $chamado->protocolo }}</span>
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
                    </div>
                    <p class="text-md font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $chamado->titulo }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Últ. At.: {{ $chamado->updated_at->format('d/m/Y H:i') }}</p>
                    <a href="{{ route('portal.chamados.show', $chamado) }}" class="mt-3 inline-block w-full text-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Ver Detalhes
                    </a>
                </div>
            @empty
                <div class="p-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Nenhum chamado encontrado.
                </div>
            @endforelse
        </div>
    </div>

    {{-- PAGINAÇÃO (ainda comentada, pois $chamados é uma Collection) --}}
    <div class="mt-6">
        {{-- {{ $chamados->links() }} --}}
    </div>

</x-portal-layout>