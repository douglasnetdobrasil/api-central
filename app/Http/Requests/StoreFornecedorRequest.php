<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Adicionamos uma regra para validar o CPF/CNPJ futuramente
        // Por enquanto, validamos apenas se é único e o tamanho
        $documentoRule = 'required|string|max:18|unique:fornecedores,cpf_cnpj';

        return [
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'tipo_pessoa' => 'required|in:fisica,juridica',
            'cpf_cnpj' => $documentoRule,
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string',
        ];
    }
}