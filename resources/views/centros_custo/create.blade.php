<div class="card mt-4" x-data="{ 
    rateios: [{ centro_custo_id: '', valor: '' }],
    valorTotal: {{ old('valor_total', 0) }},
    get totalRateado() {
        return this.rateios.reduce((total, item) => total + parseFloat(item.valor || 0), 0).toFixed(2);
    }
}">
    <div class="card-header">
        <h4>Rateio por Centro de Custo</h4>
    </div>
    <div class="card-body">
        <template x-for="(rateio, index) in rateios" :key="index">
            <div class="row align-items-center mb-2">
                <div class="col-md-6">
                    <label>Centro de Custo</label>
                    <select :name="`rateios[${index}][centro_custo_id]`" class="form-control" x-model="rateio.centro_custo_id" required>
                        <option value="">Selecione...</option>
                        @foreach($centrosCustoDisponiveis as $cc)
                            <option value="{{ $cc->id }}">{{ $cc->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Valor</label>
                    <input type="number" :name="`rateios[${index}][valor]`" class="form-control" x-model="rateio.valor" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger mt-4" @click="rateios.splice(index, 1)" x-show="rateios.length > 1">Remover</button>
                </div>
            </div>
        </template>
        
        <button type="button" class="btn btn-success mt-2" @click="rateios.push({ centro_custo_id: '', valor: '' })">
            Adicionar Rateio
        </button>

        <div class="mt-3">
            <strong>Valor Total da Conta:</strong> <span x-text="parseFloat(valorTotal || 0).toFixed(2)"></span><br>
            <strong>Total Rateado:</strong> <span x-text="totalRateado"></span>
            <div class="alert alert-danger mt-1" x-show="totalRateado != parseFloat(valorTotal || 0).toFixed(2) && valorTotal > 0">
                O total rateado é diferente do valor total da conta!
            </div>
        </div>
    </div>
</div>