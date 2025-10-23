<div x-data="{ openModalEdit: false, modalClienteIdEdit: {{ $ordemServico->cliente_id }} }"
     class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Cliente --}}
    <div>
        <label for="cliente_id_edit" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Cliente *</label>
        <select name="cliente_id" id="cliente_id_edit" class="select-search mt-1 block w-full" required>
            @foreach ($clientes as $cliente)
                <option value="{{ $cliente->id }}" {{ old('cliente_id', $ordemServico->cliente_id) == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->nome }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- CAMPO EQUIPAMENTO ATUALIZADO (com botão e carregamento correto) --}}
    <div>
        <label for="cliente_equipamento_id_edit" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Equipamento *</label>
        <div class="flex items-center space-x-2 mt-1">
            <select name="cliente_equipamento_id" 
                    id="cliente_equipamento_id_edit" 
                    class="block w-full" 
                    required>
                <option value="">Selecione um equipamento...</option>
                
                {{-- Carrega os equipamentos com Blade na inicialização --}}
                @foreach ($equipamentosDoCliente as $equip)
                    <option value="{{ $equip->id }}" {{ $ordemServico->cliente_equipamento_id == $equip->id ? 'selected' : '' }}>
                        {{ $equip->descricao }} {{ $equip->numero_serie ? '(SN: '.$equip->numero_serie.')' : '' }}
                    </option>
                @endforeach
            </select>
            {{-- Botão para abrir o modal --}}
            <button type="button" id="btn_novo_equipamento_edit"
                    @click.prevent="if(modalClienteIdEdit) { openModalEdit = true; } else { alert('Por favor, selecione um cliente primeiro.'); }"
                    class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-green-700">
                + Novo
            </button>
        </div>
    </div>

    {{-- Campos ocultos para o controller --}}
    <input type="hidden" name="equipamento" id="equipamento_edit_hidden" value="{{ $ordemServico->equipamento }}">
    <input type="hidden" name="numero_serie" id="numero_serie_edit_hidden" value="{{ $ordemServico->numero_serie }}">


    {{-- Técnico --}}
    <div>
        <label for="tecnico_id_edit" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Técnico Responsável</label>
        <select name="tecnico_id" id="tecnico_id_edit" class="select-search mt-1 block w-full">
            <option value="">(Não atribuído)</option>
            @foreach ($tecnicos as $tecnico)
                <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $ordemServico->tecnico_id) == $tecnico->id ? 'selected' : '' }}>
                    {{ $tecnico->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Status --}}
    <div>
        <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status *</label>
        <select name="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
            @php
                $statuses = ['Aberta', 'Aguardando Aprovação', 'Aprovada', 'Em Execução', 'Aguardando Peças', 'Concluída', 'Faturada', 'Cancelada'];
            @endphp
            @foreach ($statuses as $status)
                <option value="{{ $status }}" {{ old('status', $ordemServico->status) == $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    {{-- Previsão de Conclusão --}}
    <div>
        <label for="data_previsao_conclusao" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Previsão de Conclusão</label>
        <input type="date" name="data_previsao_conclusao" id="data_previsao_conclusao" value="{{ old('data_previsao_conclusao', $ordemServico->data_previsao_conclusao ? \Carbon\Carbon::parse($ordemServico->data_previsao_conclusao)->format('Y-m-d') : '') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
    </div>

    {{-- Defeito Relatado (Linha inteira) --}}
    <div class="md:col-span-2">
        <label for="defeito_relatado" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Defeito Relatado *</label>
        <textarea name="defeito_relatado" id="defeito_relatado" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>{{ old('defeito_relatado', $ordemServico->defeito_relatado) }}</textarea>
    </div>

    {{-- Laudo Técnico (Linha inteira) --}}
    <div class="md:col-span-2">
        <label for="laudo_tecnico" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Laudo Técnico</label>
        <textarea name="laudo_tecnico" id="laudo_tecnico" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('lao_tecnico', $ordemServico->laudo_tecnico) }}</textarea>
    </div>

    {{-- ====================================================== --}}
    {{-- ||||||||||||||||||| HTML DO MODAL ||||||||||||||||||||| --}}
    {{-- ====================================================== --}}
    {{-- Este div não afeta o layout pois está "display: none" --}}
    <div x-show="openModalEdit" @keydown.escape.window="openModalEdit = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background Overlay --}}
            <div x-show="openModalEdit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="openModalEdit = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            {{-- Conteúdo do Modal --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
            <div x-show="openModalEdit" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div id="form_novo_equipamento_edit">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            Cadastrar Novo Equipamento
                        </h3>
                        <div class="mt-4 space-y-4">
                            <div id="modal_error_message_edit" class="hidden p-3 bg-red-100 text-red-700 border border-red-300 rounded-md"></div>
                            <input type="hidden" id="modal_cliente_id_edit" x-model="modalClienteIdEdit">
                            
                            <div>
                                <label for="modal_descricao_edit" class="block text-sm font-medium">Descrição *</label>
                                <input type="text" id="modal_descricao_edit" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="modal_numero_serie_edit" class="block text-sm font-medium">Nº de Série / IMEI</label>
                                <input type="text" id="modal_numero_serie_edit" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="modal_marca_edit" class="block text-sm font-medium">Marca</label>
                                    <input type="text" id="modal_marca_edit" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label for="modal_modelo_edit" class="block text-sm font-medium">Modelo</label>
                                    <input type="text" id="modal_modelo_edit" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" id="btn_salvar_modal_edit"
                                onclick="salvarNovoEquipamentoEdit()"
                                class="w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button" @click="openModalEdit = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> {{-- Fim do x-data --}}


{{-- ====================================================== --}}
{{-- ||||||||||||||||||| BLOCO SCRIPT ||||||||||||||||||||| --}}
{{-- ====================================================== --}}
{{-- O script fica FORA do div principal para não interferir --}}

{{-- Garante que JQuery e TomSelect estejam carregados --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    // Variáveis globais para os seletores TomSelect
    var tomSelectClienteEdit, tomSelectTecnicoEdit, tomSelectEquipamentoEdit;

    /**
     * Função para salvar o novo equipamento (via modal)
     */
    function salvarNovoEquipamentoEdit() {
        var btnSalvar = $('#btn_salvar_modal_edit');
        var errorDiv = $('#modal_error_message_edit');
        btnSalvar.prop('disabled', true).text('Salvando...');
        errorDiv.hide().empty();

        $.ajax({
            url: '{{ route("os.equipamentos.storeModal") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                cliente_id: $('#modal_cliente_id_edit').val(),
                descricao: $('#modal_descricao_edit').val(),
                numero_serie: $('#modal_numero_serie_edit').val(),
                marca: $('#modal_marca_edit').val(),
                modelo: $('#modal_modelo_edit').val()
            },
            success: function(equipamento) {
                // Adiciona a nova opção no select e já a seleciona
                tomSelectEquipamentoEdit.addOption({ value: equipamento.id, text: equipamento.texto });
                tomSelectEquipamentoEdit.setValue(equipamento.id);
                
                // Fecha o modal (usando o Alpine.js)
                var alpineComponent = document.querySelector('[x-data]');
                if (alpineComponent) {
                    alpineComponent.__x.data.openModalEdit = false;
                }

                // Limpa os campos do modal
                $('#modal_descricao_edit, #modal_numero_serie_edit, #modal_marca_edit, #modal_modelo_edit').val('');
                btnSalvar.prop('disabled', false).text('Salvar');
            },
            error: function(xhr) {
                // Exibe os erros de validação
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

    /**
     * Função para carregar equipamentos do cliente selecionado
     */
    function carregarEquipamentosEdit(clienteId) {
        // Atualiza o ID do cliente no Alpine (para o modal)
        var alpineComponent = document.querySelector('[x-data]');
        if (alpineComponent) {
            alpineComponent.__x.data.modalClienteIdEdit = clienteId;
        }
        
        tomSelectEquipamentoEdit.clear();
        tomSelectEquipamentoEdit.clearOptions();

        if (!clienteId) {
            tomSelectEquipamentoEdit.addOption({ value: '', text: 'Selecione o cliente primeiro...' });
            tomSelectEquipamentoEdit.disable();
            return;
        }

        tomSelectEquipamentoEdit.enable();
        tomSelectEquipamentoEdit.load(function(callback) {
            var url = `/os/clientes/${clienteId}/equipamentos`; 
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    tomSelectEquipamentoEdit.addOption({ value: '', text: 'Selecione um equipamento...' });
                    
                    if (data.length > 0) {
                        data.forEach(function(equip) {
                            tomSelectEquipamentoEdit.addOption({ value: equip.id, text: equip.texto });
                        });
                    }
                    callback();
                }).catch(() => {
                    callback();
                });
        });
    }

    /**
     * Bloco principal que roda quando o documento está pronto
     */
    $(document).ready(function() {
        if (typeof TomSelect !== 'undefined') {
            
            // Inicializa o TomSelect do Técnico
            tomSelectTecnicoEdit = new TomSelect('#tecnico_id_edit', { create: false });
            
            // Inicializa o TomSelect de Equipamento (já vem populado pelo Blade)
            tomSelectEquipamentoEdit = new TomSelect('#cliente_equipamento_id_edit', { create: false });
            
            // Inicializa o TomSelect de Cliente
            tomSelectClienteEdit = new TomSelect('#cliente_id_edit', {
                create: false,
                onChange: carregarEquipamentosEdit // Chama a função ao trocar cliente
            });

        } else {
            console.error("TomSelect não foi carregado.");
        }
    });
</script>