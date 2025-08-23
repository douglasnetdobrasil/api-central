<?php

namespace Database\Factories;

use App\Models\Compra;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemCompraFactory extends Factory
{
    public function definition(): array
    {
        $quantidade = $this->faker->numberBetween(1, 10);
        $precoCusto = $this->faker->randomFloat(2, 5, 100);

        return [
            'compra_id' => Compra::factory(),
            'descricao_item_nota' => 'Produto de Teste da Nota',
            'quantidade' => $quantidade,
            'preco_custo_nota' => $precoCusto,
            'subtotal' => $quantidade * $precoCusto,
        ];
    }
}