<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'fornecedor_id' => Fornecedor::factory(),
            'status' => 'digitacao',
            'numero_nota' => $this->faker->unique()->numberBetween(1000, 9999),
            'serie_nota' => '1',
            'data_emissao' => now()->subDays(5),
            'data_chegada' => now(),
            'valor_total_nota' => $this->faker->randomFloat(2, 100, 5000),
        ];
    }
}