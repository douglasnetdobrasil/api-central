<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdutoMercadoRequest extends FormRequest
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
            'nome' => 'required|string|max:255',
            'preco_venda' => 'required|numeric|min:0.01',
            
            // Validação para o objeto de detalhes do mercado
            'detalhes' => 'required|array',
            'detalhes.marca' => 'nullable|string|max:100',
            'detalhes.codigo_barras' => 'nullable|string|max:100|unique:detalhes_item_mercado,codigo_barras',
            'detalhes.preco_custo' => 'nullable|numeric|min:0',
            'detalhes.estoque_atual' => 'required|numeric|min:0',
            'detalhes.unidade_medida_id' => 'required|exists:unidades_medida,id',
            
            // Validação para o objeto de dados fiscais
            'dados_fiscais' => 'sometimes|required|array', // `sometimes` torna o objeto opcional
            'dados_fiscais.ncm' => 'required_with:dados_fiscais|string|max:10',
            'dados_fiscais.origem' => 'required_with:dados_fiscais|string|max:1',
            'dados_fiscais.cfop' => 'nullable|string|max:4',
        ];
    }
}