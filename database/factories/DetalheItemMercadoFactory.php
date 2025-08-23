<?php

namespace Database\Factories;

use App\Models\CategoriaProduto;
use App\Models\Fornecedor;
use App\Models\UnidadeMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalheItemMercadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'marca' => $this->faker->company(),
            'codigo_barras' => $this->faker->unique()->ean13(),
            'preco_custo' => $this->faker->randomFloat(2, 5, 50),
            'estoque_atual' => $this->faker->numberBetween(50, 200),
            'estoque_minimo' => $this->faker->numberBetween(10, 20),
            'unidade_medida_id' => UnidadeMedida::factory(),
            'categoria_id' => CategoriaProduto::factory(),
            'fornecedor_id' => Fornecedor::factory(),
            'controla_validade' => false,
            'vendido_por_peso' => false,
        ];
    }
}