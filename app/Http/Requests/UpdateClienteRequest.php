<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
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
        // Pega o cliente da rota para poder ignorá-lo na verificação de campos únicos
        $clienteId = $this->route('cliente')->id;

        return [
            'nome' => 'sometimes|required|string|max:255',
            'cpf_cnpj' => [
                'sometimes',
                'nullable',
                'string',
                'max:18',
                Rule::unique('clientes')->ignore($clienteId),
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('clientes')->ignore($clienteId),
            ],
            'telefone' => 'sometimes|nullable|string|max:20',
            'data_nascimento' => 'sometimes|nullable|date',
            'endereco_completo' => 'sometimes|nullable|string',
        ];
    }
}