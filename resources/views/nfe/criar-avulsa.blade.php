<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ isset($nfe) ? 'Editar Rascunho de NF-e' : 'Criar Nova NF-e Avulsa' }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'info' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Navegação das Abas --}}
            <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2">
                        <a href="#" @click.prevent="tab = 'info'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'info'}" class="inline-block p-4 border-b-2 rounded-t-lg">Informações</a>
                    </li>
                    <li class="mr-2">
                        <a href="#" @click.prevent="tab = 'produtos'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'produtos'}" class="inline-block p-4 border-b-2 rounded-t-lg">Produtos</a>
                    </li>
                    <li class="mr-2">
                        <a href="#" @click.prevent="tab = 'transporte'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'transporte'}" class="inline-block p-4 border-b-2 rounded-t-lg">Transporte</a>
                    </li>
                     <li class="mr-2">
                        <a href="#" @click.prevent="tab = 'pagamento'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'pagamento'}" class="inline-block p-4 border-b-2 rounded-t-lg">Pagamento e Totais</a>
                    </li>
                </ul>
            </div>

            {{-- Formulário Principal --}}
            <form action="{{ isset($nfe) ? route('nfe.rascunho.update', $nfe) : route('nfe.rascunho.store') }}" method="POST">
                @csrf
                @if(isset($nfe))
                    @method('PUT')
                @endif

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">

                    {{-- ABA 1: INFORMAÇÕES GERAIS E CLIENTE (Já estava correta) --}}
                    <div x-show="tab === 'info'" class="space-y-6">
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                             <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Dados Gerais</h3>
                             <div class="mt-4 grid md:grid-cols-3 gap-4">
                                <div>
                                    <label for="natureza_operacao" class="block text-sm dark:text-gray-300">Natureza da Operação</label>
                                    <input type="text" name="natureza_operacao" value="{{ old('natureza_operacao', $nfe->natureza_operacao ?? 'VENDA DE MERCADORIAS') }}" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600" required>
                                </div>
                                <div>
                                    <label for="serie" class="block text-sm dark:text-gray-300">Série</label>
                                    <input type="number" name="serie" value="{{ old('serie', $nfe->serie ?? 1) }}" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600" required>
                                </div>
                                <div>
                                    <label class="block text-sm dark:text-gray-300">Número da Nota</label>
                                    <input type="text" value="{{ $nfe->numero_nfe ?? 'Automático' }}" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 rounded-md" disabled>
                                </div>
                             </div>
                        </div>
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Destinatário (Cliente)</h3>
                            <div class="mt-4">
                                <select name="cliente_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600" required>
                                    <option value="">Selecione um cliente...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id', $nfe->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nome }} ({{ $cliente->cpf_cnpj }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ABA 2: PRODUTOS (CÓDIGO COMPLETO AGORA) --}}
                    <div x-show="tab === 'produtos'">
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Adicionar Produtos / Itens</h3>
                            <div class="mt-4 p-4 border border-dashed dark:border-gray-600 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                    <div class="md:col-span-5"><label for="produto_select" class="block text-sm dark:text-gray-300">Produto</label><select id="produto_select" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600"><option value="">Selecione...</option>@foreach($produtos as $produto)<option value="{{ $produto->id }}" data-preco="{{ $produto->preco_venda }}">{{ $produto->nome }}</option>@endforeach</select></div>
                                    <div class="md:col-span-2"><label for="quantidade" class="block text-sm dark:text-gray-300">Qtd.</label><input type="number" id="quantidade" step="0.001" value="1" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600"></div>
                                    <div class="md:col-span-3"><label for="preco" class="block text-sm dark:text-gray-300">Preço Unit. (R$)</label><input type="number" id="preco" step="0.01" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600"></div>
                                    <div class="md:col-span-2"><button type="button" id="add-produto-btn" class="w-full justify-center py-2 px-4 rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Adicionar</button></div>
                                </div>
                            </div>
                            <div class="mt-6 overflow-x-auto"><table class="min-w-full divide-y dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produto</th><th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qtd</th><th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Vlr. Unit.</th><th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th><th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Ação</th></tr></thead><tbody id="produtos-tbody" class="dark:bg-gray-800 divide-y dark:divide-gray-700"></tbody></table></div>
                        </div>
                    </div>

                    {{-- ABA 3: TRANSPORTE (CÓDIGO COMPLETO AGORA) --}}
                    <div x-show="tab === 'transporte'">
                         <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Transporte / Frete</h3>
                            <div class="mt-4 grid md:grid-cols-3 gap-6">
                                <div>
                                    <label for="modalidade_frete" class="block text-sm dark:text-gray-300">Modalidade do Frete</label>
                                    <select name="modalidade_frete" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">
                                        <option value="9" {{ old('modalidade_frete', $nfe->modalidade_frete ?? '9') == '9' ? 'selected' : '' }}>9 - Sem Frete</option>
                                        <option value="0" {{ old('modalidade_frete', $nfe->modalidade_frete ?? '') == '0' ? 'selected' : '' }}>0 - Por conta do Remetente (CIF)</option>
                                        <option value="1" {{ old('modalidade_frete', $nfe->modalidade_frete ?? '') == '1' ? 'selected' : '' }}>1 - Por conta do Destinatário (FOB)</option>
                                        <option value="2" {{ old('modalidade_frete', $nfe->modalidade_frete ?? '') == '2' ? 'selected' : '' }}>2 - Por conta de Terceiros</option>
                                        <option value="3" {{ old('modalidade_frete', $nfe->modalidade_frete ?? '') == '3' ? 'selected' : '' }}>3 - Transporte Próprio por conta do Remetente</option>
                                        <option value="4" {{ old('modalidade_frete', $nfe->modalidade_frete ?? '') == '4' ? 'selected' : '' }}>4 - Transporte Próprio por conta do Destinatário</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="transportadora_id" class="block text-sm dark:text-gray-300">Transportadora</label>
                                    <select name="transportadora_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">
                                        <option value="">Nenhuma transportadora</option>
                                        {{-- @foreach($transportadoras as $transportadora)
                                            <option value="{{ $transportadora->id }}">{{ $transportadora->nome }}</option>
                                        @endforeach --}}
                                    </select>
                                </div>
                                <div>
                                    <label for="frete_valor" class="block text-sm dark:text-gray-300">Valor do Frete (R$)</label>
                                    <input type="number" step="0.01" name="frete_valor" value="{{ old('frete_valor', $nfe->frete_valor ?? '0.00') }}" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">
                                </div>
                            </div>
                            <div class="mt-4 grid md:grid-cols-4 gap-4">
                                <div>
                                    <label for="frete_volumes" class="block text-sm dark:text-gray-300">Qtd. Volumes</label>
                                    <input type="number" name="frete_volumes" value="{{ old('frete_volumes', $nfe->frete_volumes ?? '') }}" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">
                                </div>
                                <div>
                                    <label for="frete_especie" class="block text-sm dark:text-gray-300">Espécie</label>
                                    <input type="text" name="frete_especie" value="{{ old('frete_especie', $nfe->frete_especie ?? '') }}" placeholder="Ex: Caixas, Paletes" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">
                                </div>
                                <div>
                                    <label for="frete_peso_bruto" class="block text-sm dark:text-gray-300">Peso Bruto (Kg)</label>
                                    <input type="number" step="0.001" name="frete_peso_bruto" value="{{ old('frete_peso_bruto', $nfe->frete_peso_bruto ?? '') }}" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">
                                </div>
                                <div>
                                    <label for="frete_peso_liquido" class="block text-sm dark:text-gray-300">Peso Líquido (Kg)</label>
                                    <input type="number" step="0.001" name="frete_peso_liquido" value="{{ old('frete_peso_liquido', $nfe->frete_peso_liquido ?? '') }}" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ABA 4: PAGAMENTO e OBSERVAÇÕES (CÓDIGO COMPLETO AGORA) --}}
                    <div x-show="tab === 'pagamento'" class="space-y-6">
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Formas de Pagamento</h3>
                             {{-- Aqui você pode adicionar uma lógica para inserir as formas de pagamento --}}
                             <div class="mt-4 text-gray-400">
                                Funcionalidade de pagamento a ser implementada. Por padrão, será salvo como "Outros".
                             </div>
                        </div>
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Totais e Observações</h3>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="observacoes" class="block text-sm dark:text-gray-300">Informações Adicionais (Observações)</label>
                                    <textarea name="observacoes" rows="4" class="mt-1 block w-full rounded-md dark:bg-gray-900 dark:border-gray-600">{{ old('observacoes', $nfe->observacoes ?? '') }}</textarea>
                                </div>
                                <div class="flex items-end justify-end">
                                    <div class="text-right">
                                        <p class="text-sm text-gray-400">VALOR TOTAL DA NOTA</p>
                                        <p class="text-3xl font-bold dark:text-white" id="total-geral">R$ 0,00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BOTÕES DE AÇÃO --}}
                    <div class="flex items-center justify-end gap-4 pt-6 border-t dark:border-gray-700">
                        <button type="submit" name="action" value="save_draft" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 rounded-md text-white">Salvar Rascunho</button>
                        <button type="submit" name="action" value="issue_now" class="px-6 py-3 bg-green-600 hover:bg-green-700 rounded-md font-semibold text-white">Salvar e Emitir NF-e</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<script>
// Este script é idêntico ao da resposta anterior, ele já está pronto para funcionar com a nova estrutura.
document.addEventListener('DOMContentLoaded', function () {
    const produtoSelect = document.getElementById('produto_select');
    const quantidadeInput = document.getElementById('quantidade');
    const precoInput = document.getElementById('preco');
    const addBtn = document.getElementById('add-produto-btn');
    const tbody = document.getElementById('produtos-tbody');
    const totalGeralEl = document.getElementById('total-geral');
    let produtoIndex = 0;

    produtoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        precoInput.value = selectedOption.dataset.preco || '';
    });

    addBtn.addEventListener('click', function() {
        const produtoId = produtoSelect.value;
        const produtoNome = produtoSelect.options[produtoSelect.selectedIndex].text;
        const quantidade = parseFloat(quantidadeInput.value);
        const preco = parseFloat(precoInput.value);

        if (!produtoId || isNaN(quantidade) || quantidade <= 0 || isNaN(preco) || preco < 0) {
            alert('Por favor, selecione um produto e preencha a quantidade e o preço corretamente.');
            return;
        }

        const subtotal = quantidade * preco;
        
        const newRow = tbody.insertRow();
        newRow.id = `produto-row-${produtoIndex}`;
        newRow.innerHTML = `
            <td class="px-4 py-3 whitespace-nowrap">${produtoNome}</td>
            <td class="px-4 py-3 text-center">${quantidade.toLocaleString('pt-BR')}</td>
            <td class="px-4 py-3 text-right">${preco.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
            <td class="px-4 py-3 text-right">${subtotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
            <td class="px-4 py-3 text-center"><button type="button" class="text-red-500 hover:text-red-700" onclick="removerProduto(${produtoIndex})">Remover</button></td>
            <input type="hidden" name="produtos[${produtoIndex}][id]" value="${produtoId}"><input type="hidden" name="produtos[${produtoIndex}][quantidade]" value="${quantidade}"><input type="hidden" name="produtos[${produtoIndex}][preco]" value="${preco}">
        `;
        
        produtoIndex++;
        atualizarTotalGeral();
        
        produtoSelect.value = '';
        quantidadeInput.value = '1';
        precoInput.value = '';
    });

    window.removerProduto = function(index) {
        document.getElementById(`produto-row-${index}`).remove();
        atualizarTotalGeral();
    }

    function atualizarTotalGeral() {
        let total = 0;
        const rows = tbody.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const inputs = rows[i].getElementsByTagName('input');
            const quantidade = parseFloat(inputs[1].value);
            const preco = parseFloat(inputs[2].value);
            total += quantidade * preco;
        }
        totalGeralEl.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
});
</script>
</x-app-layout>