<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Central de Emissão de NF-e
        </h2>
    </x-slot>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    @if (session('success') || session('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 my-4" role="alert">
            <p class="font-bold">Sucesso</p>
            <p>{{ session('success') ?? session('message') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-4" role="alert">
            <p class="font-bold">Erro</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif
</div>

    <div class="py-12" x-data="{ tab: 'emitidas' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- CABEÇALHO COM BUSCA E BOTÃO DE EMISSÃO --}}
            <div class="bg-white dark:bg-gray-800 p-4 shadow-sm sm:rounded-lg">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    {{-- Formulário de Busca --}}
                    <form action="{{ route('nfe.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Pesquisar..." class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-gray-200 border rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">Buscar</button>
                    </form>

                    {{-- Botão de Emitir NF-e (Dropdown) --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                            <span>Emitir NF-e</span>
                            <svg class="ml-2 -mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-10" style="display: none;">
                            <div class="py-1">
                                <a href="{{ route('nfe.importarPedidos') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">A partir de Pedido(s) de Venda</a>
                                <a href="{{ route('nfe.avulsa.criar') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Emissão Avulsa (nova)</a>
                                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">NF-e Complementar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NAVEGAÇÃO DAS ABAS --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <a href="#" @click.prevent="tab = 'emitidas'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'emitidas'}" class="inline-block p-4 border-b-2 rounded-t-lg">Notas Emitidas</a>
                        </li>
                        <li class="mr-2">
                            <a href="#" @click.prevent="tab = 'rascunhos'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'rascunhos'}" class="inline-block p-4 border-b-2 rounded-t-lg">
                                Rascunhos (Em Digitação)
                                @if($rascunhos->total() > 0)
                                    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">{{ $rascunhos->total() }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- CONTEÚDO DA ABA "NOTAS EMITIDAS" --}}
                <div x-show="tab === 'emitidas'" class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            {{-- Seu cabeçalho de tabela original --}}
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº NFe / Série</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Emissão</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {{-- Seu loop de notas emitidas original --}}
                                @forelse ($notasEmitidas as $nfe)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $nfe->numero_nfe }} / {{ $nfe->serie }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $nfe->venda->cliente->nome ?? 'N/A' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $nfe->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">R$ {{ number_format($nfe->venda->total ?? 0, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($nfe->status == 'autorizada') bg-green-100 text-green-800 @elseif($nfe->status == 'erro') bg-red-100 text-red-800 @elseif($nfe->status == 'cancelada') bg-yellow-100 text-yellow-800 @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst($nfe->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm font-medium">
                                            {{-- Seu menu de opções original --}}
                                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                                <div><button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none">Opções<svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></button></div>
                                                <div x-show="open" @click.away="open = false" x-transition class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-10" style="display: none;"><div class="py-1">
                                                    <a href="{{ route('nfe.danfe', $nfe) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Ver DANFE</a>
                                                    <a href="{{ route('nfe.xml', $nfe) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Baixar XML</a>
                                                    @if($nfe->cces->isNotEmpty())
                                                        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                                        @foreach($nfe->cces as $cce)<a href="{{ route('nfe.cce.pdf', $cce) }}" target="_blank" class="block px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-600">Ver CC-e #{{ $cce->sequencia_evento }}</a>@endforeach
                                                    @endif
                                                    @if ($nfe->status == 'autorizada')
                                                        <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                                        <button @click="$dispatch('open-cce-modal', { id: {{ $nfe->id }} }); open = false" type="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Carta de Correção</button>
                                                        <button @click="$dispatch('open-cancel-modal', { id: {{ $nfe->id }} }); open = false" type="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600">Cancelar NF-e</button>
                                                    @endif
                                                </div></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Nenhuma NF-e emitida encontrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $notasEmitidas->withQueryString()->links() }}</div>
                </div>

                {{-- CONTEÚDO DA ABA "RASCUNHOS" --}}
                <div x-show="tab === 'rascunhos'" class="p-6 text-gray-900 dark:text-gray-100" style="display: none;">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID da Venda</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Última Alteração</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($rascunhos as $rascunho)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $rascunho->id }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $rascunho->cliente->nome ?? 'N/A' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $rascunho->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4 text-right whitespace-nowrap">R$ {{ number_format($rascunho->total, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-center text-sm font-medium">
                                            <a href="{{ route('nfe.avulsa.editar', $rascunho) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                Continuar Digitação
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                     <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum rascunho encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">{{ $rascunhos->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    

    {{-- MODAL DE CANCELAMENTO --}}
    <div x-data="{ open: false, nfeId: null, actionUrl: '' }" 
         @open-cancel-modal.window="open = true; nfeId = $event.detail.id; actionUrl = '{{ route('nfe.index') }}/' + nfeId + '/cancelar'" 
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div x-show="open" x-transition 
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <form :action="actionUrl" method="POST">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                            Cancelar NF-e
                        </h3>
                        <div class="mt-2">
                            <label for="justificativa" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Justificativa (mínimo 15 caracteres)
                            </label>
                            <textarea id="justificativa" name="justificativa" rows="4" required minlength="15"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirmar Cancelamento
                        </button>
                        <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Fechar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- MODAL DA CARTA DE CORREÇÃO (CC-e) --}}
    <div x-data="{ open: false, nfeId: null, actionUrl: '' }" 
         @open-cce-modal.window="open = true; nfeId = $event.detail.id; actionUrl = '{{ route('nfe.index') }}/' + nfeId + '/cce'" 
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div x-show="open" x-transition 
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <form :action="actionUrl" method="POST">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                            Emitir Carta de Correção (CC-e)
                        </h3>
                        <div class="mt-2">
                            <label for="correcao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Texto da Correção (mínimo 15 caracteres)
                            </label>
                            <textarea id="correcao" name="correcao" rows="6" required minlength="15" maxlength="1000"
                                      placeholder="Ex: Altera-se o número do endereço do destinatário de 123 para 321."
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Enviar CC-e
                        </button>
                        <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Fechar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>