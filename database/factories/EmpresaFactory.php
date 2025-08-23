<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'razao_social' => $this->faker->company(),
            'cnpj' => $this->faker->unique()->numerify('##.###.###/####-##'),
            'nicho_negocio' => 'mercado',
        ];
    }
}