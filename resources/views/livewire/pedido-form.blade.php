<div>
    {{-- AQUI VAI A INTERFACE DE EDIÇÃO --}}
    {{-- Você pode se basear na view do seu PDV (pdv.blade.php) para construir esta parte. --}}
    {{-- Principais seções: --}}
    
    <div class="bg-white dark:bg-gray-800 shadow-md p-4 mb-4 rounded-lg">
        <h3 class="font-bold">Cliente: {{ $venda->cliente->nome }}</h3>
    </div>

    {{-- ... código da busca de produtos ... --}}

    {{-- ... código da tabela do carrinho ... --}}

    {{-- ... código dos totais e do campo de observações ... --}}

    <div class="bg-white dark:bg-gray-800 shadow-md p-4 mt-4 rounded-lg">
        <h3 class="font-bold mb-2">Pagamentos</h3>
        {{-- Aqui você pode reusar a lógica do MODAL do PDV, mas diretamente na página --}}
        {{-- Listar pagamentos adicionados, formulário para adicionar novo, etc. --}}
    </div>

    <div class="mt-6 flex justify-end">
        <x-primary-button wire:click="finalizarPedido" class="text-lg">
            Salvar e Finalizar Pedido
        </x-primary-button>
    </div>
</div>