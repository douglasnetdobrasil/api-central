<?php

namespace App\Http\Requests;

use App\Models\Produto;
use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => 'required|integer|exists:clientes,id',
            'vendedor_id' => 'required|integer|exists:usuarios,id',
            'observacao' => 'nullable|string|max:1000',
            
            // Regras para a lista de itens
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|integer|exists:produtos,id',
            'itens.*.quantidade' => [
                'required',
                'numeric',
                'gt:0', // Garante que a quantidade seja maior que zero
                
                // Validação customizada para verificar o estoque
                function ($attribute, $value, $fail) {
                    // Pega o índice do item que está sendo validado (ex: 0, 1, 2...)
                    $index = explode('.', $attribute)[1];

                    // Pega o ID do produto para este item
                    $produtoId = $this->input("itens.$index.produto_id");
                    
                    if (!$produtoId) {
                        return; // A validação 'exists' já vai pegar esse erro
                    }

                    $produto = Produto::find($produtoId);
                    
                    // Verifica se o produto tem um detalhe com estoque
                    if ($produto && isset($produto->detalhe->estoque_atual)) {
                        if ($produto->detalhe->estoque_atual < $value) {
                            $fail("Estoque insuficiente para o produto {$produto->nome}. Disponível: {$produto->detalhe->estoque_atual}.");
                        }
                    }
                },
            ],
            'itens.*.preco_unitario_venda' => 'required|numeric|min:0',
        ];
    }
}