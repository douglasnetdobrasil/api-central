<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fornecedor_id' => 'required|integer|exists:fornecedores,id',
            'numero_nota' => 'required|string|max:50',
            'serie_nota' => 'nullable|string|max:10',
            'data_emissao' => 'required|date',
            'valor_total_nota' => 'required|numeric|min:0',
            'observacoes' => 'nullable|string',

            'itens' => 'required|array|min:1',
            'itens.*.descricao_item_nota' => 'required|string|max:255',
            'itens.*.quantidade' => 'required|numeric|gt:0',
            'itens.*.preco_custo_nota' => 'required|numeric|min:0',
            'itens.*.subtotal' => 'required|numeric|min:0',
        ];
    }
}