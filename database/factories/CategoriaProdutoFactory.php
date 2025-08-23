<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaProdutoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => $this->faker->words(2, true), // Gera 2 palavras aleatórias
        ];
    }
}