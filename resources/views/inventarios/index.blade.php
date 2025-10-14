<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestão de Inventários
            </h2>
            <a href="{{ route('inventarios.create') }}">
                <x-primary-button>
                    Iniciar Novo Inventário
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">#ID</th>
                                    <th class="px-4 py-2 text-left">Data Início</th>
                                    <th class="px-4 py-2 text-left">Responsável</th>
                                    <th class="px-4 py-2 text-center">Status</th>
                                    <th class="px-4 py-2 text-left">Data Conclusão</th>
                                    <th class="px-4 py-2 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventarios as $inventario)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">{{ $inventario->id }}</td>
                                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($inventario->data_inicio)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2">{{ $inventario->responsavel->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @switch($inventario->status)
                                                    @case('em_andamento') bg-yellow-100 text-yellow-800 @break
                                                    @case('contado') bg-blue-100 text-blue-800 @break
                                                    @case('finalizado') bg-green-100 text-green-800 @break
                                                    @default bg-gray-100 text-gray-800
                                                @endswitch">
                                                {{ ucfirst(str_replace('_', ' ', $inventario->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">{{ $inventario->data_conclusao ? \Carbon\Carbon::parse($inventario->data_conclusao)->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="px-4 py-2 text-right">
                                            @if($inventario->status == 'em_andamento')
                                                <a href="{{ route('inventarios.contagem', $inventario) }}" class="text-indigo-600 hover:text-indigo-900">Continuar Contagem</a>
                                            @elseif($inventario->status == 'contado')
                                                <a href="{{ route('inventarios.reconciliacao', $inventario) }}" class="text-green-600 hover:text-green-900">Revisar e Finalizar</a>
                                            @else
                                            <a href="{{ route('inventarios.visualizar', $inventario) }}" class="text-gray-500 hover:text-gray-700">Visualizar</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Nenhum inventário encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $inventarios->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>