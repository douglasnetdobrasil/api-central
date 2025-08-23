<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProdutoMercadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Pega o ID do produto da rota para ignorá-lo na verificação de 'unique'
        $produtoId = $this->route('produto')->detalhe->id;

        return [
            'nome' => 'sometimes|required|string|max:255',
            'preco_venda' => 'sometimes|required|numeric|min:0.01',

            'detalhes' => 'sometimes|required|array',
            'detalhes.marca' => 'nullable|string|max:100',
            'detalhes.codigo_barras' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('detalhes_item_mercado')->ignore($produtoId),
            ],
            // ... outras regras de 'detalhes' que você queira permitir a atualização

            'dados_fiscais' => 'sometimes|required|array',
            'dados_fiscais.ncm' => 'sometimes|required|string|max:10',
            // ... outras regras fiscais
        ];
    }
}