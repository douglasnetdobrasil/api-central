<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fornecedor>
 */
class FornecedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'razao_social' => $this->faker->company() . ' LTDA',
            'nome_fantasia' => $this->faker->company(),
            'tipo_pessoa' => 'juridica', // <-- Campo obrigatório
            'cpf_cnpj' => $this->faker->unique()->numerify('##.###.###/####-##'), // <-- Campo obrigatório
            'email' => $this->faker->unique()->companyEmail(),
            'telefone' => $this->faker->phoneNumber(),
            'endereco' => $this->faker->address(),
        ];
    }
}