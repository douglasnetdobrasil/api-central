<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadeMedidaFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Usando uma palavra estática + número aleatório para garantir unicidade
            'nome' => 'Item Teste ' . $this->faker->unique()->randomNumber(6),

            // CORRIGIDO: Substituindo o método problemático do Faker por PHP puro
            'sigla' => substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2),
        ];
    }
}