<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $produtoId = $this->route('produto'); // Pega o ID do produto da rota

        return [
            'nome' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'codigo_barras' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('produtos')->ignore($produtoId),
            ],
            'preco_custo' => 'sometimes|nullable|numeric|min:0',
            'preco_venda' => 'sometimes|required|numeric|min:0.01',
            'estoque_atual' => 'sometimes|required|numeric|min:0',
            'unidade_medida_id' => 'sometimes|required|exists:unidades_medida,id',
            'categoria_id' => 'sometimes|nullable|exists:categorias_produto,id',
            'fornecedor_id' => 'sometimes|nullable|exists:fornecedores,id',
            'ativo' => 'sometimes|boolean',
        ];
    }
}