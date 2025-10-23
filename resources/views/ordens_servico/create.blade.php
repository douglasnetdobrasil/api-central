<x-app-layout>
    <x-slot name="head">
        {{-- Nenhum CSS necessário aqui --}}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Criar Nova Ordem de Serviço') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- 
                        ======================================================================
                        |||||||||||||||||||||||||||| MUDANÇA AQUI ||||||||||||||||||||||||||||
                        ======================================================================
                        1. Removemos o id="create-os-form" (não é mais necessário)
                        2. Adicionamos "@set-client-id.window" para ouvir o evento do JS
                        3. Adicionamos "@close-create-modal.window" para fechar o modal
                    --}}
                    <form action="{{ route('ordens-servico.store') }}" method="POST"
                          x-data="{ openModalCreate: false, modalClienteIdCreate: null }"
                          @set-client-id.window="modalClienteIdCreate = $event.detail.clientId"
                          @close-create-modal.window="openModalCreate = false">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Campo Cliente (Select HTML Padrão) - Sem alteração --}}
                            <div>
                                <label for="cliente_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Cliente *</label>
                                <select name="cliente_id" id="cliente_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Selecione um cliente...</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nome }} {{ $cliente->cpf_cnpj ? '('.$cliente->cpf_cnpj.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- CAMPO EQUIPAMENTO (COM BOTÃO "+ Novo") --}}
                            <div>
                                <label for="cliente_equipamento_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Equipamento (Existente)</label>
                                {{-- DIV FLEX ADICIONADO PARA O BOTÃO --}}
                                <div class="flex items-center space-x-2 mt-1">
                                    <select name="cliente_equipamento_id" 
                                            id="cliente_equipamento_id" 
                                            class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" 
                                            disabled>
                                        <option value="">Selecione o cliente primeiro...</option>
                                    </select>
                                    {{-- Botão para abrir o modal de CRIAÇÃO --}}
                                    <button type="button" id="btn_novo_equipamento_create"
                                            @click.prevent="if(modalClienteIdCreate) { openModalCreate = true; } else { alert('Por favor, selecione um cliente primeiro.'); }"
                                            class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-green-700">
                                        + Novo
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecione um equipamento existente ou clique em "+ Novo".</p>
                            </div>

                            {{-- Campos de texto para Equipamento/Série (Sem alteração) --}}
                            <div>
                                <label for="equipamento" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Descrição Equipamento *</label>
                                <input type="text" name="equipamento" id="equipamento" value="{{ old('equipamento') }}" placeholder="Ex: Notebook Dell Vostro" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Descreva o equipamento que está entrando.</p>
                                @error('equipamento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="numero_serie" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nº de Série / IMEI</label>
                                <input type="text" name="numero_serie" id="numero_serie" value="{{ old('numero_serie') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            </div>

                            {{-- Campo Técnico (Select HTML Padrão) - Sem alteração --}}
                            <div>
                                <label for="tecnico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Técnico Responsável</label>
                                <select name="tecnico_id" id="tecnico_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="">(Opcional)</option>
                                    @foreach ($tecnicos as $tecnico)
                                        <option value="{{ $tecnico->id }}" {{ old('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                            {{ $tecnico->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                           {{-- Demais campos sem alteração --}}
                           <div class="md:col-span-2"> <label for="defeito_relatado" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Defeito Relatado *</label> <textarea name="defeito_relatado" id="defeito_relatado" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>{{ old('defeito_relatado') }}</textarea> </div>
                           <div> <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status Inicial *</label> <select name="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required> <option value="Aberta" selected>Aberta</option> <option value="Aguardando Orçamento">Aguardando Orçamento</option> <option value="Aguardando Aprovação">Aguardando Aprovação</option> <option value="Aguardando Peças">Aguardando Peças</option> </select> </div>
                           <div> <label for="data_previsao_conclusao" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Previsão de Conclusão</label> <input type="date" name="data_previsao_conclusao" id="data_previsao_conclusao" min="{{ date('Y-m-d') }}" value="{{ old('data_previsao_conclusao') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"> </div>
                       </div>

                        {{-- Botões de Ação --}}
                        <div class="mt-6 flex justify-end"> <a href="{{ route('ordens-servico.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-500"> Cancelar </a> <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-indigo-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-indigo-700"> Criar Ordem </button> </div>
                    
                        {{-- ====================================================== --}}
                        {{-- ||||||||||||||| HTML DO MODAL DE CRIAÇÃO ||||||||||||||| --}}
                        {{-- ====================================================== --}}
                        <div x-show="openModalCreate" @keydown.escape.window="openModalCreate = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                {{-- Background Overlay --}}
                                <div x-show="openModalCreate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="openModalCreate = false">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                                </div>
                                
                                {{-- Conteúdo do Modal --}}
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                                <div x-show="openModalCreate" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    
                                    <div id="form_novo_equipamento_create">
                                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                                Cadastrar Novo Equipamento
                                            </h3>
                                            <div class="mt-4 space-y-4">
                                                <div id="modal_error_message_create" class="hidden p-3 bg-red-100 text-red-700 border border-red-300 rounded-md"></div>
                                                {{-- Input hidden que pega o ID do cliente do Alpine --}}
                                                <input type="hidden" id="modal_cliente_id_create" x-model="modalClienteIdCreate">
                                                
                                                <div>
                                                    <label for="modal_descricao_create" class="block text-sm font-medium">Descrição *</label>
                                                    <input type="text" id="modal_descricao_create" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                </div>
                                                <div>
                                                    <label for="modal_numero_serie_create" class="block text-sm font-medium">Nº de Série / IMEI</label>
                                                    <input type="text" id="modal_numero_serie_create" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="modal_marca_create" class="block text-sm font-medium">Marca</label>
                                                        <input type="text" id="modal_marca_create" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                    </div>
                                                    <div>
                                                        <label for="modal_modelo_create" class="block text-sm font-medium">Modelo</label>
                                                        <input type="text" id="modal_modelo_create" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="button" id="btn_salvar_modal_create"
                                                    onclick="salvarNovoEquipamentoCreate()"
                                                    class="w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                                                Salvar
                                            </button>
                                            <button type="button" @click="openModalCreate = false"
                                                    class="mt-3 w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                    {{-- Fim do x-data --}}

                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- ||||||||||||| JAVASCRIPT HÍBRIDO (Vanilla + jQuery) ||||||||||||| --}}
    {{-- ========================================================== --}}
    @push('scripts')
        {{-- ADICIONADO JQUERY (Necessário para o $.ajax do modal) --}}
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        {{-- SCRIPT DO MODAL (jQuery/AJAX) --}}
        <script>
            /**
             * Função para salvar o novo equipamento (via modal)
             * Usa jQuery/AJAX
             */
            function salvarNovoEquipamentoCreate() {
                var btnSalvar = $('#btn_salvar_modal_create');
                var errorDiv = $('#modal_error_message_create');
                btnSalvar.prop('disabled', true).text('Salvando...');
                errorDiv.hide().empty();

                $.ajax({
                    url: '{{ route("os.equipamentos.storeModal") }}', // Usa a mesma rota
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cliente_id: $('#modal_cliente_id_create').val(),
                        descricao: $('#modal_descricao_create').val(),
                        numero_serie: $('#modal_numero_serie_create').val(),
                        marca: $('#modal_marca_create').val(),
                        modelo: $('#modal_modelo_create').val()
                    },
                    success: function(equipamento) {
                        // 'equipamento' retorna { id: 123, texto: "Descricao (SN: 123)" }

                        // *** INÍCIO DA INTEGRAÇÃO COM VANILLA JS ***
                        const selectEquipamento = document.getElementById('cliente_equipamento_id');
                        
                        // 1. Remove a opção "(Nenhum equipamento cadastrado)" se ela existir
                        const noEquipOption = selectEquipamento.querySelector('option[disabled]');
                        if (noEquipOption) {
                            noEquipOption.remove();
                        }
                        // 2. Cria a nova opção
                        const newOption = document.createElement('option');
                        newOption.value = equipamento.id;
                        newOption.text = equipamento.texto;
                        
                        // 3. Adiciona e seleciona a nova opção
                        selectEquipamento.appendChild(newOption);
                        selectEquipamento.value = equipamento.id;

                        // 4. DISPARA O EVENTO 'change' (para sua lógica vanilla JS pegar)
                        selectEquipamento.dispatchEvent(new Event('change'));
                        // *** FIM DA INTEGRAÇÃO ***
                        
                        
                        // ==========================================================
                        // |||||||||||||||||||||||| MUDANÇA AQUI ||||||||||||||||||||||||
                        // ==========================================================
                        // Fecha o modal (usando o Alpine.js via Custom Event)
                        // Removemos a linha: var alpineComponent = document.getElementById('create-os-form');
                        window.dispatchEvent(new Event('close-create-modal'));
                        

                        // Limpa os campos do modal (jQuery)
                        $('#modal_descricao_create, #modal_numero_serie_create, #modal_marca_create, #modal_modelo_create').val('');
                        btnSalvar.prop('disabled', false).text('Salvar');
                    },
                    error: function(xhr) {
                        // Exibe os erros de validação (jQuery)
                        var errors = xhr.responseJSON.errors;
                        var errorMsg = "Ocorreram erros:<ul>";
                        $.each(errors, function(key, value) {
                            errorMsg += "<li>" + value[0] + "</li>";
                        });
                        errorMsg += "</ul>";
                        errorDiv.html(errorMsg).show();
                        btnSalvar.prop('disabled', false).text('Salvar');
                    }
                });
            }
        </script>
        
        {{-- SEU SCRIPT VANILLA JS (Modificado para integrar com Alpine) --}}
        <script>
            console.log("Script create.blade.php (Híbrido) DENTRO DO PUSH carregado!");

            /**
             * Função Vanilla JS SIMPLIFICADA para carregar equipamentos existentes
             */
            function carregarEquipamentos(clienteId) {
                console.log("[carregarEquipamentos Híbrido] Cliente ID:", clienteId);
                
                // ==========================================================
                // |||||||||||||||||||||||| MUDANÇA AQUI ||||||||||||||||||||||||
                // ==========================================================
                // *** INTEGRAÇÃO ALPINE.JS ***
                // Dispara um evento global que o Alpine vai ouvir
                window.dispatchEvent(new CustomEvent('set-client-id', { 
                    detail: { clientId: clienteId } 
                }));
                // *** FIM DA INTEGRAÇÃO ***

                const selectEquipamento = document.getElementById('cliente_equipamento_id');
                selectEquipamento.innerHTML = ''; // Limpa

                if (!clienteId) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.text = 'Selecione o cliente primeiro...';
                    selectEquipamento.appendChild(option);
                    selectEquipamento.disabled = true;
                    return;
                }

                selectEquipamento.disabled = false;
                const loadingOption = document.createElement('option');
                loadingOption.value = '';
                loadingOption.text = 'Carregando...';
                selectEquipamento.appendChild(loadingOption);

                const url = `/os/clientes/${clienteId}/equipamentos`;
                console.log("[carregarEquipamentos Híbrido] Fetch URL:", url);

                fetch(url)
                    .then(response => {
                        console.log("[carregarEquipamentos Híbrido] Fetch status:", response.status);
                        if (!response.ok) { throw new Error(`Erro ${response.status}`); }
                        return response.json();
                    })
                    .then(data => {
                        console.log("[carregarEquipamentos Híbrido] Dados recebidos:", data);
                        selectEquipamento.innerHTML = ''; // Limpa "Carregando..."
                        const defaultOption = document.createElement('option');
                        defaultOption.value = ''; // Valor vazio para não pré-selecionar
                        defaultOption.text = '-- Selecione se houver --';
                        selectEquipamento.appendChild(defaultOption);

                        if (data && data.length > 0) {
                            data.forEach(equip => {
                                const option = document.createElement('option');
                                option.value = equip.id;
                                option.text = equip.texto; // Usa o texto formatado (Descricao (SN: ...))
                                selectEquipamento.appendChild(option);
                            });
                        } else {
                             const noEquipOption = document.createElement('option');
                             noEquipOption.value = '';
                             noEquipOption.text = '(Nenhum equipamento cadastrado)';
                             noEquipOption.disabled = true; // Desabilita a opção
                             selectEquipamento.appendChild(noEquipOption);
                        }
                    }).catch((error) => {
                        console.error('[carregarEquipamentos Híbrido] ERRO:', error);
                        selectEquipamento.innerHTML = '';
                        const errorOption = document.createElement('option');
                        errorOption.value = '';
                        errorOption.text = 'Erro ao carregar';
                        selectEquipamento.appendChild(errorOption);
                    });
            }

            // Adiciona o Listener quando o DOM estiver pronto (Vanilla JS)
            document.addEventListener('DOMContentLoaded', (event) => {
                console.log("Vanilla JS Híbrido: DOMContentLoaded!");

                const clienteSelect = document.getElementById('cliente_id');
                const equipamentoSelect = document.getElementById('cliente_equipamento_id');

                if (clienteSelect) {
                    // Listener PADRÃO no select de Cliente
                    clienteSelect.addEventListener('change', function() {
                        console.log("Vanilla JS Híbrido: Cliente selecionado:", this.value);
                        carregarEquipamentos(this.value);
                        // Limpa os campos de texto se mudar o cliente
                        document.getElementById('equipamento').value = '';
                        document.getElementById('numero_serie').value = '';
                    });

                    // Verifica no load da página
                    const clienteInicialId = clienteSelect.value;
                    console.log("Vanilla JS Híbrido: Cliente inicial:", clienteInicialId);
                    if (clienteInicialId) {
                        
                        // ==========================================================
                        // |||||||||||||||||||||||| MUDANÇA AQUI ||||||||||||||||||||||||
                        // ==========================================================
                        // *** INTEGRAÇÃO ALPINE.JS ***
                        // Dispara o evento global no carregamento da página
                        window.dispatchEvent(new CustomEvent('set-client-id', { 
                            detail: { clientId: clienteInicialId } 
                        }));
                        // *** FIM DA INTEGRAÇÃO ***
                        
                        setTimeout(() => { carregarEquipamentos(clienteInicialId); }, 50); // Pequeno delay
                    }
                }

                // NOVO: Preenche os campos de texto ao selecionar um equipamento existente
                if(equipamentoSelect) {
                    equipamentoSelect.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const equipId = this.value;

                        if (equipId && selectedOption.text !== '-- Selecione se houver --' && !selectedOption.disabled) {
                             // Extrai Descrição e SN do texto da opção selecionada
                             let texto = selectedOption.text;
                             let descricao = texto;
                             let sn = '';
                             const snIndex = texto.lastIndexOf(' (SN: ');
                             if (snIndex > -1) {
                                 descricao = texto.substring(0, snIndex);
                                 sn = texto.substring(snIndex + 6, texto.length - 1); // Remove ' (SN: ' e ')'
                             }
                             document.getElementById('equipamento').value = descricao;
                             document.getElementById('numero_serie').value = sn;
                             console.log("Preencheu campos com:", descricao, sn);
                        } else {
                             // Se selecionou "Selecione..." ou "Nenhum...", limpa os campos
                             document.getElementById('equipamento').value = '';
                             document.getElementById('numero_serie').value = '';
                             console.log("Limpou campos de texto.");
                        }
                    });
                }

            });
        </script>
    @endpush
</x-app-layout>