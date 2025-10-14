<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Ordens de Produção
            </h2>
            <a href="{{ route('ordem-producao.create') }}">
                <x-primary-button>
                    Nova Ordem de Produção
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">OP #</th>
                                    <th class="px-4 py-2 text-left">Produto</th>
                                    <th class="px-4 py-2 text-center">Qtd. Planejada</th>
                                    <th class="px-4 py-2 text-center">Status</th>
                                    <th class="px-4 py-2 text-left">Data Criação</th>
                                    <th class="px-4 py-2 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
    @forelse ($ordensProducao as $op)
        <tr>
            <td class="px-4 py-2 font-bold">{{ $op->id }}</td>
            <td class="px-4 py-2">{{ $op->produtoAcabado->nome }}</td>
            <td class="px-4 py-2 text-center">{{ number_format($op->quantidade_planejada, 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-center">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ $op->status }}
                </span>
            </td>
            <td class="px-4 py-2">{{ $op->created_at->format('d/m/Y H:i') }}</td>
            <td class="px-4 py-2 text-right">
                <div class="flex justify-end items-center space-x-4">
                    <a href="{{ route('ordem-producao.show', $op) }}" class="text-indigo-600 hover:text-indigo-900">
                        Detalhes
                    </a>

                    {{-- ======================================================= --}}
                    {{-- |||||||||||||||| BOTÃO EXCLUIR ADICIONADO ||||||||||||||| --}}
                    {{-- ======================================================= --}}
                    @if ($op->status == 'Planejada')
                        <form action="{{ route('ordem-producao.destroy', $op) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta Ordem de Produção? Esta ação não pode ser desfeita.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center py-4">Nenhuma Ordem de Produção encontrada.</td>
        </tr>
    @endforelse
</tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $ordensProducao->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>