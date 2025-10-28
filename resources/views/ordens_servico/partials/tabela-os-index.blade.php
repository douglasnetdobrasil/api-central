{{-- Este arquivo é para a tabela da página de listagem (index) --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">OS #</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Equipamento</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data Entrada</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($ordensServico as $os)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $os->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->cliente->nome }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->equipamento }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->data_entrada->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($os->status == 'Concluída') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @elseif($os->status == 'Cancelada') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                            @elseif(in_array($os->status, ['Aguardando Aprovação', 'Aguardando Peças'])) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @endif">
                            {{ $os->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    {{-- INÍCIO DO DROP DOWN DE AÇÕES --}}
    <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
        
        {{-- BOTÃO PRINCIPAL (Toggle) --}}
        <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 dark:border-gray-700 shadow-sm px-3 py-2 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Ações
            {{-- Ícone da seta --}}
            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        {{-- PAINEL DO DROP DOWN --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-100" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-75" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95" 
             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-10" 
             style="display: none;" 
             role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
            
            <div class="py-1" role="none">
                
                {{-- 1. Opção: Editar --}}
                <a href="{{ route('ordens-servico.edit', $os) }}" class="text-gray-700 dark:text-gray-300 block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">
                    <i class="fas fa-edit mr-2"></i> Editar OS
                </a>
                
                {{-- 2. Opção: Imprimir --}}
                <a href="{{ route('ordens-servico.imprimir', $os) }}" target="_blank" class="text-gray-700 dark:text-gray-300 block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">
                    <i class="fas fa-print mr-2"></i> Imprimir OS
                </a>
                
                {{-- Separador Condicional --}}
                @if ($os->status === 'Concluida' || $os->venda_id)
                    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                @endif
                
                {{-- 3. Opção Condicional: FATURAR RÁPIDO (Aparece se CONCLUÍDA e NÃO FATURADA) --}}
                @if ($os->status === 'Concluida' && $os->venda_id === null)
                    <button wire:click="faturarRapido({{ $os->id }})" 
                            onclick="confirm('Confirma faturamento rápido (Venda à vista, hoje) da OS #{{ $os->id }}?') || event.stopImmediatePropagation()"
                            class="text-green-600 dark:text-green-400 block w-full text-left px-4 py-2 text-sm hover:bg-green-50 dark:hover:bg-green-900" 
                            role="menuitem">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Faturar Rápido
                    </button>
                @elseif ($os->venda_id)
                    {{-- 4. Opção Condicional: VER VENDA (Aparece se JÁ FATURADA) --}}
                    <a href="{{ route('vendas.show', $os->venda_id) }}" 
                       class="text-blue-600 dark:text-blue-400 block px-4 py-2 text-sm hover:bg-blue-50 dark:hover:bg-blue-900"
                       role="menuitem">
                        <i class="fas fa-receipt mr-2"></i> Ver Venda #{{ $os->venda_id }}
                    </a>
                @endif
                
            </div>
        </div>
    </div>
    {{-- FIM DO DROP DOWN DE AÇÕES --}}
</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">
                        Nenhuma Ordem de Serviço encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $ordensServico->links() }}
</div>