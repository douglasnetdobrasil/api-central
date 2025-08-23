<?php

use App\Models\Fornecedor;
use App\Models\Usuario;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\DetalheItemMercado;
use App\Models\Empresa; // <-- LINHA IMPORTANTE
use App\Models\Produto;


test('deve ser capaz de criar uma pre-nota de compra em digitacao', function () {
    // Arrange: Prepara o cenário
    $empresa = \App\Models\Empresa::factory()->create();
    $usuario = Usuario::factory()->create(['empresa_id' => $empresa->id]);
    $fornecedor = Fornecedor::factory()->create();

    $dadosCompra = [
        'fornecedor_id' => $fornecedor->id,
        'numero_nota' => '12345',
        'serie_nota' => '1',
        'data_emissao' => '2025-08-10',
        'valor_total_nota' => 350.00,
        'itens' => [
            [
                'descricao_item_nota' => 'Produto A da Nota',
                'quantidade' => 2,
                'preco_custo_nota' => 100.00,
                'subtotal' => 200.00
            ],
            [
                'descricao_item_nota' => 'Produto B da Nota',
                'quantidade' => 1,
                'preco_custo_nota' => 150.00,
                'subtotal' => 150.00
            ]
        ]
    ];

    // Act: Faz a chamada para a API autenticado como o usuário criado
    $response = actingAs($usuario, 'sanctum')->postJson('/api/compras', $dadosCompra);

    // Assert: Verifica se tudo aconteceu como esperado
    $response->assertStatus(201);
    $response->assertJsonFragment(['numero_nota' => '12345']);

    // Verifica se o cabeçalho da compra foi salvo com o status correto
    $this->assertDatabaseHas('compras', [
        'fornecedor_id' => $fornecedor->id,
        'numero_nota' => '12345',
        'status' => 'digitacao' // O mais importante!
    ]);

    // Verifica se os itens da compra foram salvos
    $this->assertDatabaseHas('itens_compra', [
        'descricao_item_nota' => 'Produto A da Nota',
        'quantidade' => 2
    ]);
    $this->assertDatabaseCount('itens_compra', 2);
});