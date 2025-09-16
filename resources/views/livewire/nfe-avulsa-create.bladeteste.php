{{-- Arquivo: resources/views/livewire/nfe-avulsa-create.blade.php (Versão de TESTE) --}}
<div>
    <h1>Teste de Busca de Produtos</h1>

    {{-- Div para conter a busca, essencial para o posicionamento da lista --}}
    <div style="position: relative; width: 300px; margin-top: 20px;">
        
        <label for="produtoSearch">Adicionar Produto</label>
        
        {{-- O input de busca que não está funcionando no seu código original --}}
        <input 
            wire:model.live.debounce.300ms="produtoSearch" 
            type="text" 
            id="produtoSearch"
            placeholder="Digite o ID, nome ou código..."
            style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
        />
        
        {{-- A lista de resultados --}}
        @if(!empty($produtosEncontrados))
            <ul style="position: absolute; z-index: 10; width: 100%; margin-top: 5px; background-color: white; border: 1px solid #ddd; border-radius: 4px; list-style: none; padding: 0;">
                @foreach($produtosEncontrados as $p)
                    <li wire:click="adicionarProduto({{ $p->id }})" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;">
                        {{ $p->nome }}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <hr style="margin: 20px 0;">

    <h2>Carrinho</h2>
    <ul>
        @forelse($cart as $item)
            <li>{{ $item['nome'] }} - Qtd: {{ $item['quantidade'] }}</li>
        @empty
            <li>Nenhum produto adicionado.</li>
        @endforelse
    </ul>

</div>