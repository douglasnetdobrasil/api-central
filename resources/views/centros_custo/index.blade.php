<x-app-layout> <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Centros de Custo') }}
        </h2>
    </x-slot>

    <h1>Centros de Custo</h1>
    <a href="{{ route('centros-custo.create') }}" class="btn btn-primary mb-3">Novo Centro de Custo</a>

    <div class="tree-container">
        <ul class="list-group">
            @foreach ($centrosCusto as $centro)
                @include('centros_custo._item_tree', ['item' => $centro])
            @endforeach
        </ul>
    </div>

</x-app-layout>