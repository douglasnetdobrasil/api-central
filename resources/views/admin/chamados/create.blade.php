<x-app-layout> {{-- Usa o layout de Admin --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Abrir Novo Chamado (Admin)
        </h2>
    </x-slot>

    {{-- INÍCIO DA ESTRUTURA ALPINE/MODAL --}}
    <div x-data="{ openModal: false }" @close-modal.window="openModal = false">

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <form action="{{ route('admin.chamados.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">

                            {{-- 1. SELEÇÃO DE CLIENTE (Campo de Disparo) --}}
                            <div>
                                <x-input-label for="cliente_id" value="Cliente *" />
                                <select name="cliente_id" id="cliente_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    required>
                                    <option value="">-- Selecione o Cliente --</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
                            </div>

                            {{-- 2. EQUIPAMENTO (DINÂMICO + BOTÃO MODAL) --}}
                            <div>
                                <div class="flex items-center gap-3">
                                    <x-input-label for="cliente_equipamento_id" value="Equipamento (Opcional)" />
                                    
                                    {{-- Botão que abre o modal. Controlado por JS/Alpine. --}}
                                    <button type="button" 
                                            @click.prevent="openModal = true"
                                            id="btn-novo-equipamento" 
                                            class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 disabled:opacity-50 transition duration-150"
                                            disabled>
                                        + Cadastrar Novo Equipamento
                                    </button>
                                </div>
                                <div class="mt-1">
                                    <select name="cliente_equipamento_id" id="cliente_equipamento_id" 
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" disabled>
                                        <option value="">-- Selecione um cliente primeiro --</option>
                                    </select>
                                </div>
                                <x-input-error :messages="$errors->get('cliente_equipamento_id')" class="mt-2" />
                            </div>
                            
                            {{-- CAMPOS OCULTOS PARA EQUIPAMENTO E SÉRIE (Se o Admin escolher um SN) --}}
                            <input type="hidden" name="equipamento" id="equipamento" value="{{ old('equipamento') }}">
                            <input type="hidden" name="numero_serie" id="numero_serie" value="{{ old('numero_serie') }}">
                            
                            {{-- Título e Prioridade --}}
                            <div>
                                <x-input-label for="titulo" value="Título *" />
                                <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo')" required />
                                <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="prioridade" value="Prioridade *" />
                                <select name="prioridade" id="prioridade" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    @foreach($prioridades as $p)
                                        <option value="{{ $p }}" {{ old('prioridade', 'Média') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('prioridade')" class="mt-2" />
                            </div>

                            {{-- Descrição do Problema --}}
                            <div>
                                <x-input-label for="descricao_problema" value="Descreva o problema *" />
                                <textarea id="descricao_problema" name="descricao_problema" rows="8" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>{{ old('descricao_problema') }}</textarea>
                                <x-input-error :messages="$errors->get('descricao_problema')" class="mt-2" />
                            </div>

                            {{-- Anexos --}}
                            <div>
                                <x-input-label for="anexos" value="Anexar arquivos (Opcional)" />
                                <input type="file" name="anexos[]" id="anexos" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"/>
                                <x-input-error :messages="$errors->get('anexos.*')" class="mt-2" />
                            </div>

                            <div class="flex justify-end">
                                <x-primary-button>
                                    Abrir Chamado
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- ====================================================== --}}
        {{-- ||||||||||||||| MODAL NOVO EQUIPAMENTO (HTML) ||||||||||||||| --}}
        {{-- ====================================================== --}}
        <div x-show="openModal" @keydown.escape.window="openModal = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-on:click="openModal = false" class="fixed inset-0 bg-gray-900/50 dark:bg-gray-900/80 backdrop-blur-sm"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <div id="form-novo-equipamento">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">
                                Cadastrar Novo Equipamento
                            </h3>
                            
                            <div id="modal-validation-errors" class="hidden p-3 mb-4 bg-red-100 border border-red-400 text-red-700 rounded-md"></div>
                            
                            <form id="form-novo-equipamento-modal">
                                <input type="hidden" id="modal_cliente_id" name="cliente_id">
                                
                                <div class="mt-3">
                                    <x-input-label for="modal_descricao" value="Descrição *" />
                                    <x-text-input id="modal_descricao" name="descricao" type="text" class="mt-1 block w-full" required />
                                </div>
                                <div class="mt-3">
                                    <x-input-label for="modal_numero_serie" value="Nº de Série" />
                                    <x-text-input id="modal_numero_serie" name="numero_serie" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="modal_marca" value="Marca" />
                                        <x-text-input id="modal_marca" name="marca" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <x-input-label for="modal_modelo" value="Modelo" />
                                        <x-text-input id="modal_modelo" name="modelo" type="text" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <x-primary-button id="btn-salvar-equipamento-modal" class="ml-3" onclick="salvarEquipamentoModal()">
                                Salvar
                            </x-primary-button>
                            <button type="button" x-on:click="openModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-700 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> {{-- Fim do x-data --}}


    {{-- ====================================================== --}}
    {{-- ||||||||||||||| SCRIPT DE COMPORTAMENTO DINÂMICO ||||||||||||||| --}}
    {{-- ====================================================== --}}
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            $(document).ready(function() {
                const clienteSelect = $('#cliente_id');
                const equipamentoSelect = $('#cliente_equipamento_id');
                const btnNovoEquipamento = $('#btn-novo-equipamento');

                // Lógica 1: Carrega equipamentos ao selecionar o cliente
                clienteSelect.on('change', function() {
                    const clienteId = $(this).val();
                    const urlCarregar = "{{ route('admin.chamados.equipamentosPorCliente') }}";
                    
                    // 1. Habilita/Desabilita o botão "+ Novo" e o campo oculto do modal
                    btnNovoEquipamento.prop('disabled', !clienteId); 
                    $('#modal_cliente_id').val(clienteId); 

                    equipamentoSelect.prop('disabled', true).html('<option value="">Carregando...</option>');

                    if (!clienteId) {
                        equipamentoSelect.html('<option value="">-- Selecione o cliente primeiro --</option>');
                        return;
                    }

                    // Busca equipamentos via AJAX
                    $.ajax({
                        url: urlCarregar,
                        type: 'GET',
                        data: { cliente_id: clienteId },
                        success: function(data) {
                            equipamentoSelect.prop('disabled', false).html('<option value="">-- Selecione se houver --</option>');

                            if(data && data.length > 0) {
                                data.forEach(function(equip) {
                                    equipamentoSelect.append(new Option(equip.texto, equip.id));
                                });
                            } else {
                                equipamentoSelect.append('<option value="" disabled>Nenhum equipamento cadastrado</option>');
                            }

                            // Tenta re-selecionar o equipamento antigo e dispara a Lógica 2
                            const oldEquipmentId = '{{ old('cliente_equipamento_id') }}';
                            if(oldEquipmentId) {
                                equipamentoSelect.val(oldEquipmentId).trigger('change');
                            }
                        },
                        error: function(error) {
                            console.error('Erro ao buscar equipamentos:', error);
                            equipamentoSelect.html('<option value="">Erro ao carregar</option>');
                        }
                    });
                }).trigger('change'); 


                // Lógica 2: Preenche os campos de texto/ocultos ao selecionar o equipamento
                equipamentoSelect.on('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const equipId = this.value;

                    if (equipId && selectedOption.text !== '-- Selecione se houver --' && !selectedOption.disabled) {
                         // Lógica de extração de SN do texto da opção (igual à da OS)
                         let texto = selectedOption.text;
                         let descricao = texto;
                         let sn = '';
                         const snIndex = texto.lastIndexOf(' (SN: ');
                         
                         if (snIndex > -1) {
                             descricao = texto.substring(0, snIndex);
                             sn = texto.substring(snIndex + 6, texto.length - 1); 
                         }
                         
                         // Preenche os campos ocultos do formulário principal
                         document.getElementById('equipamento').value = descricao;
                         document.getElementById('numero_serie').value = sn;
                    } else {
                         document.getElementById('equipamento').value = '';
                         document.getElementById('numero_serie').value = '';
                    }
                });
            });


            /**
             * Função para salvar o novo equipamento (via modal) na área ADMIN
             */
            function salvarEquipamentoModal() { 
                const btnSalvar = $('#btn-salvar-equipamento-modal');
                const errorDiv = $('#modal-validation-errors');
                const clienteId = $('#modal_cliente_id').val(); // Pega o ID do cliente selecionado
                const urlSalvar = "{{ route('admin.equipamentos.storeModal') }}";
                
                if (!clienteId) {
                    errorDiv.html('Erro: Cliente não selecionado no formulário principal.').show();
                    return;
                }

                btnSalvar.prop('disabled', true).text('Salvando...');
                errorDiv.hide().empty();

                // Dados do modal
                const formData = {
                    _token: '{{ csrf_token() }}',
                    cliente_id: clienteId, // Envia o ID do cliente (diferencial do Admin)
                    descricao: $('#modal_descricao').val(),
                    numero_serie: $('#modal_numero_serie').val(),
                    marca: $('#modal_marca').val(),
                    modelo: $('#modal_modelo').val(),
                };

                $.ajax({
                    url: urlSalvar, 
                    type: 'POST',
                    data: formData,
                    success: function(equipamento) {
                        const selectEquipamento = document.getElementById('cliente_equipamento_id');

                        // Adiciona a nova opção no select e a seleciona
                        $(selectEquipamento).find('option[disabled]').remove(); 
                        $(selectEquipamento).find('option[value=\"\"]').remove(); 
                        
                        const newOption = new Option(equipamento.texto, equipamento.id, true, true);
                        selectEquipamento.appendChild(newOption);

                        // Dispara mudança para preencher os campos ocultos do form
                        $('#cliente_equipamento_id').trigger('change');

                        // Fecha o modal
                        window.dispatchEvent(new CustomEvent('close-modal')); 
                        
                        // Limpa campos e reativa o botão
                        $('#modal_descricao, #modal_numero_serie, #modal_marca, #modal_modelo').val('');
                        btnSalvar.prop('disabled', false).text('Salvar');
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                        if(errors){
                            var errorMsg = "Ocorreram erros de validação:<ul>";
                            $.each(errors, function(key, value) {
                                errorMsg += "<li>" + value[0] + "</li>";
                            });
                            errorMsg += "</ul>";
                            errorDiv.html(errorMsg).show();
                        } else {
                            errorDiv.html('Erro ao salvar. Tente novamente.').show();
                        }
                        btnSalvar.prop('disabled', false).text('Salvar');
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>