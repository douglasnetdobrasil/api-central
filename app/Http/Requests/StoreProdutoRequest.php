<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Qualquer usuário autenticado pode criar, por enquanto
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'codigo_barras' => 'nullable|string|max:100|unique:produtos,codigo_barras',
            'preco_custo' => 'nullable|numeric|min:0',
            'preco_venda' => 'required|numeric|min:0.01',
            'estoque_atual' => 'required|numeric|min:0',
            'unidade_medida_id' => 'required|exists:unidades_medida,id',
            'categoria_id' => 'nullable|exists:categorias_produto,id',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'ativo' => 'sometimes|boolean',
        ];
    }
}