<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gerenciar Ficha Técnica: <span class="font-bold">{{ $produto->nome }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensagens de sucesso ou erro --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">Adicionar Novo Ingrediente</h3>
                    
                    {{-- ======================================================= --}}
                    {{-- |||||||||||||||||||| LINHA CORRIGIDA |||||||||||||||||||| --}}
                    {{-- ======================================================= --}}
                    <form method="POST" action="{{ route('ficha-tecnica.storeItem', ['produto' => $produto->id]) }}" class="mt-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <label for="materia_prima_id" class="block text-sm font-medium">Matéria-Prima</label>
                                <select name="materia_prima_id" class="mt-1 block w-full rounded-md shadow-sm" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($materiasPrimas as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="quantidade" class="block text-sm font-medium">Quantidade</label>
                                <input type="number" step="0.0001" name="quantidade" class="mt-1 block w-full rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <x-primary-button type="submit">Adicionar à Receita</x-primary-button>
                            </div>
                        </div>
                         @if ($errors->any())
                            <div class="mt-2 text-sm text-red-600">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Ingredientes Atuais da Receita</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Matéria-Prima</th>
                                    <th class="px-4 py-2 text-left">Quantidade</th>
                                    <th class="px-4 py-2 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($produto->fichaTecnica as $item)
                                    <tr>
                                        <td class="px-4 py-2">{{ $item->materiaPrima->nome }}</td>
                                        <td class="px-4 py-2">{{ rtrim(rtrim(number_format($item->quantidade, 4, ',', '.'), '0'), ',') }} {{ $item->materiaPrima->unidade }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <form action="{{ route('ficha-tecnica.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Tem certeza?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">Remover</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">Nenhum ingrediente adicionado ainda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-6 border-t pt-4 text-right">
                        <a href="{{ route('ficha-tecnica.index') }}"><x-secondary-button>Voltar</x-secondary-button></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>