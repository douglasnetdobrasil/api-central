{{-- A classe 'pl-' é controlada pela variável $level para criar a indentação --}}
@php $paddingLeft = $level * 2 . 'rem'; @endphp

<div class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
    <div class="flex items-center p-2">
        {{-- Coluna Nome (com indentação) --}}
        <div class="w-2/5" style="padding-left: {{ $paddingLeft }};">
            <span class="font-semibold">{{ $item->nome }}</span>
        </div>

        {{-- Coluna Código --}}
        <div class="w-1/5 text-sm text-gray-600 dark:text-gray-400">
            {{ $item->codigo ?? '--' }}
        </div>

        {{-- Coluna Tipo --}}
        <div class="w-1/5">
            @if($item->tipo == 'SINTETICO')
                <span class="px-2 py-1 text-xs font-semibold leading-5 text-purple-800 bg-purple-100 rounded-full dark:bg-purple-700 dark:text-purple-100">Agrupador</span>
            @else
                <span class="px-2 py-1 text-xs font-semibold leading-5 text-blue-800 bg-blue-100 rounded-full dark:bg-blue-700 dark:text-blue-100">Lançamento</span>
            @endif
        </div>

        {{-- Coluna Status --}}
        <div class="w-1/5 text-center">
             @if($item->ativo)
                <span class="px-2 py-1 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">Ativo</span>
            @else
                <span class="px-2 py-1 text-xs font-semibold leading-5 text-red-800 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100">Inativo</span>
            @endif
        </div>

        {{-- Coluna Ações --}}
        <div class="w-1/5 text-right space-x-2">
            <a href="{{ route('centros-de-custo.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
            <button
                type="button"
                class="text-red-600 hover:text-red-900"
                wire:click="delete({{ $item->id }})"
                wire:confirm="Tem certeza que deseja excluir '{{ $item->nome }}'?"
            >
                Excluir
            </button>
        </div>
    </div>
</div>

{{-- Chamada Recursiva para os filhos --}}
@if ($item->children && $item->children->count() > 0)
    @foreach ($item->children as $child)
        {{-- Incrementamos o nível para aumentar a indentação --}}
        @include('centros_custo._item_tree', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif