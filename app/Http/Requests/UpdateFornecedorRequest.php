<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fornecedorId = $this->route('fornecedor')->id;

        return [
            'razao_social' => 'sometimes|required|string|max:255',
            'nome_fantasia' => 'sometimes|nullable|string|max:255',
            'tipo_pessoa' => 'sometimes|required|in:fisica,juridica',
            'cpf_cnpj' => [
                'sometimes',
                'required',
                'string',
                'max:18',
                Rule::unique('fornecedores')->ignore($fornecedorId),
            ],
            'email' => 'sometimes|nullable|email|max:255',
            'telefone' => 'sometimes|nullable|string|max:20',
            'endereco' => 'sometimes|nullable|string',
        ];
    }
}