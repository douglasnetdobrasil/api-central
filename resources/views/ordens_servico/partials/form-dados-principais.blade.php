{{-- resources/views/ordens_servico/partials/form-dados-principais.blade.php --}}

{{-- Cliente --}}
<div>
    <label for="cliente_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Cliente *</label>
    <select name="cliente_id" id="cliente_id" class="select-search mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
        @foreach ($clientes as $cliente)
            <option value="{{ $cliente->id }}" {{ old('cliente_id', $ordemServico->cliente_id) == $cliente->id ? 'selected' : '' }}>
                {{ $cliente->nome }}
            </option>
        @endforeach
    </select>
</div>

{{-- Técnico --}}
<div>
    <label for="tecnico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Técnico Responsável</label>
    <select name="tecnico_id" id="tecnico_id" class="select-search mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
        <option value="">(Não atribuído)</option>
        @foreach ($tecnicos as $tecnico)
            <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $ordemServico->tecnico_id) == $tecnico->id ? 'selected' : '' }}>
                {{ $tecnico->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- Equipamento --}}
<div>
    <label for="equipamento" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Equipamento *</label>
    <input type="text" name="equipamento" id="equipamento" value="{{ old('equipamento', $ordemServico->equipamento) }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
</div>

{{-- Número de Série --}}
<div>
    <label for="numero_serie" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nº de Série / IMEI</label>
    <input type="text" name="numero_serie" id="numero_serie" value="{{ old('numero_serie', $ordemServico->numero_serie) }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
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
    <label for="defeito_relatado" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Defeito Relatado / Observações Iniciais *</label>
    <textarea name="defeito_relatado" id="defeito_relatado" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>{{ old('defeito_relatado', $ordemServico->defeito_relatado) }}</textarea>
</div>

{{-- Laudo Técnico (Linha inteira) --}}
<div class="md:col-span-2">
    <label for="laudo_tecnico" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Laudo Técnico</label>
    <textarea name="laudo_tecnico" id="laudo_tecnico" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('laudo_tecnico', $ordemServico->laudo_tecnico) }}</textarea>
</div>