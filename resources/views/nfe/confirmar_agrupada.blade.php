<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Revisar e Emitir NF-e
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">

                    <div>
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2">
                            Destinatário
                        </h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold">Cliente:</span> {{ $cliente->nome }}
                            </div>
                            <div>
                                <span class="font-semibold">CNPJ/CPF:</span> {{ $cliente->cpf_cnpj }}
                            </div>
                            <div>
                                <span class="font-semibold">Endereço:</span> {{ $cliente->logradouro }}, {{ $cliente->numero }}
                            </div>
                            <div>
                                <span class="font-semibold">Bairro / Cidade:</span> {{ $cliente->bairro }} - {{ $cliente->municipio }} / {{ $cliente->uf }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-2">
                            Itens da Nota Fiscal
                        </h3>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qtd.</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Vlr. Unit.</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($itensAgrupados as $item)
                                    <tr>
                                        <td class="px-4 py-3">{{ $item->produto->nome }}</td>
                                        <td class="px-4 py-3 text-center">{{ $item->quantidade }}</td>
                                        <td class="px-4 py-3 text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                        {{-- CORREÇÃO APLICADA AQUI --}}
                                        <td class="px-4 py-3 text-right">R$ {{ number_format($item->subtotal_item, 2, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center">
                        <div>
                            <span class="text-lg font-semibold">Valor Total da NF-e:</span>
                            <span class="text-xl font-bold text-indigo-600">R$ {{ number_format($valorTotal, 2, ',', '.') }}</span>
                        </div>

                        {{-- CORREÇÃO APLICADA AQUI --}}
                        <form action="{{ route('nfe.store') }}" method="POST">
                            @csrf
                            @foreach ($vendaIds as $id)
                                <input type="hidden" name="venda_ids[]" value="{{ $id }}">
                            @endforeach

                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Confirmar e Emitir NF-e
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>