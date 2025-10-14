<x-app-layout>
    {{-- Garanta que o Alpine.js esteja carregado nesta página ou no layout principal --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Iniciar Novo Inventário
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- O x-data inicializa as variáveis que controlarão o formulário --}}
                    <form method="POST" action="{{ route('inventarios.store') }}" 
                          x-data="{ escopo: 'completo', subdivisao: 'categoria' }">
                        @csrf
                        {{-- ADICIONE ESTE BLOCO PARA MOSTRAR OS ERROS --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Opa!</strong>
            <span class="block sm:inline">Houve alguns problemas com os dados informados.</span>
            <ul class="mt-3 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
                        
                        <div class="space-y-6">
                            {{-- 1ª Pergunta: Completo ou Parcial? --}}
                            <div>
                                <label for="escopo" class="block text-sm font-medium">Escopo do Inventário</label>
                                <select name="escopo" id="escopo" x-model="escopo" class="mt-1 block w-full rounded-md shadow-sm">
                                    <option value="completo">Inventário Completo da Loja</option>
                                    <option value="parcial">Inventário Parcial (por setor/categoria)</option>
                                </select>
                            </div>

                            {{-- Este bloco SÓ APARECE se o escopo for 'parcial' --}}
                            <div x-show="escopo === 'parcial'" x-transition class="p-4 border rounded-md">
                                <div class="space-y-6">
                                    {{-- 2ª Pergunta: Como subdividir? --}}
                                    <div>
                                        <label for="subdivisao" class="block text-sm font-medium">Subdividir Por</label>
                                        <select name="subdivisao" id="subdivisao" x-model="subdivisao" class="mt-1 block w-full rounded-md shadow-sm">
                                            <option value="categoria">Categoria</option>
                                            <option value="setor">Setor</option>
                                        </select>
                                    </div>
    
                                    {{-- Bloco que SÓ APARECE se a subdivisão for 'categoria' --}}
                                    <div x-show="subdivisao === 'categoria'">
                                        <label for="categoria_id" class="block text-sm font-medium">Selecione a Categoria</bael>
                                        <select name="categoria_id" id="categoria_id" class="mt-1 block w-full rounded-md shadow-sm">
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                            @endforeach
                                        </select>
                                    </div>
    
                                    {{-- Bloco que SÓ APARECE se a subdivisão for 'setor' --}}
                                    <div x-show="subdivisao === 'setor'">
    <label for="setor_id" class="block text-sm font-medium">Selecione o Setor</label>
    <select name="setor_id" id="setor_id" class="mt-1 block w-full rounded-md shadow-sm">
        @foreach($setores as $setor)
            <option value="{{ $setor->id }}">{{ $setor->nome }}</option>
        @endforeach
    </select>
</div>
                                </div>
                            </div>
                            
                            {{-- Campo de Observações (sempre visível) --}}
                            <div>
                                <label for="observacoes" class="block text-sm font-medium">Observações</label>
                                <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full rounded-md shadow-sm"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button type="submit">
                                Salvar e Iniciar Contagem
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>