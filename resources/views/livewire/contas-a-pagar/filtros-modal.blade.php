<div>
    @if ($mostrarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-75" x-data="{ show: @entangle('mostrarModal') }" x-show="show" x-on:keydown.escape.window="show = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 md:p-8 w-full max-w-2xl" @click.away="show = false">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filtros e Relatórios</h3>

                <div class="space-y-4">
                    {{-- Linha 1: Fornecedor e Categoria --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="filtro_fornecedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fornecedor</label>
                            <select wire:model.defer="fornecedorId" id="filtro_fornecedor" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                <option value="">Todos</option>
                                @foreach($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->razao_social }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="filtro_categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoria</label>
                            <select wire:model.defer="categoriaId" id="filtro_categoria" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                <option value="">Todas</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Linha 2: Datas de Vencimento --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período de Vencimento</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-1">
                            <input type="date" wire:model.defer="dataVencimentoInicio" class="block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600" placeholder="Data Inicial">
                            <input type="date" wire:model.defer="dataVencimentoFim" class="block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600" placeholder="Data Final">
                        </div>
                    </div>

                    {{-- Linha 3: Status e Agrupamento --}}
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="filtro_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select wire:model.defer="status" id="filtro_status" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                <option value="">Todos</option>
                                <option value="A Pagar">A Pagar</option>
                                <option value="Paga Parcialmente">Paga Parcialmente</option>
                                <option value="Paga">Paga</option>
                                <option value="Vencida">Vencida</option>
                            </select>
                        </div>
                        <div>
                            <label for="agrupar_por" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agrupar por</label>
                            <select wire:model.defer="agruparPor" id="agrupar_por" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                <option value="">Nenhum</option>
                                <option value="categoria">Categoria</option>
                                <option value="fornecedor">Fornecedor</option>
                                <option value="data_vencimento">Data de Vencimento</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end mt-6 pt-4 border-t dark:border-gray-700 space-x-4">
                    <button wire:click="limparFiltros" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                        Limpar Filtros
                    </button>
                    <button wire:click="aplicarFiltros" class="bg-indigo-600 text-white px-4 py-2 rounded-md">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>