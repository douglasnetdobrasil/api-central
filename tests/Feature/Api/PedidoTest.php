<?php

use App\Models\Cliente;
use App\Models\DetalheItemMercado;
use App\Models\Produto;
use App\Models\Usuario;
use function Pest\Laravel\postJson;

test('deve ser capaz de registrar um novo pedido com sucesso', function () {
    // 1. Arrange: Prepara o cenário
    $cliente = Cliente::factory()->create();
    $vendedor = Usuario::factory()->create();
    $detalheProduto = DetalheItemMercado::factory()->create(['estoque_atual' => 10]);
    $produto = $detalheProduto->produto()->create(['nome' => 'Produto com Estoque', 'preco_venda' => 150.00]);

    $dadosPedido = [
        'cliente_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'itens' => [
            [
                'produto_id' => $produto->id,
                'quantidade' => 3,
                'preco_unitario_venda' => 150.00
            ]
        ]
    ];

    // 2. Act: Faz a chamada para a API
    $response = postJson('/api/pedidos', $dadosPedido);

    // 3. Assert: Verifica se tudo aconteceu como esperado
    $response->assertStatus(201);
    $response->assertJsonFragment(['nome' => 'Produto com Estoque']);
    
    // Verifica se o pedido foi criado
    $this->assertDatabaseHas('pedidos', [
        'cliente_id' => $cliente->id,
        'valor_total' => 450.00
    ]);

    // Verifica se o item do pedido foi criado
    $this->assertDatabaseHas('itens_pedido', [
        'produto_id' => $produto->id,
        'quantidade' => 3
    ]);

    // Verifica se a conta a receber foi gerada
    $this->assertDatabaseHas('contas_a_receber', [
        'valor' => 450.00
    ]);

    // Verifica se o estoque foi atualizado corretamente (10 - 3 = 7)
    $this->assertDatabaseHas('detalhes_item_mercado', [
        'id' => $detalheProduto->id,
        'estoque_atual' => 7
    ]);
});

test('não deve registrar um pedido com estoque insuficiente', function () {
    // 1. Arrange
    $cliente = Cliente::factory()->create();
    $vendedor = Usuario::factory()->create();
    $detalheProduto = DetalheItemMercado::factory()->create(['estoque_atual' => 5]); // Só 5 em estoque
    $produto = $detalheProduto->produto()->create(['nome' => 'Produto com Pouco Estoque', 'preco_venda' => 100.00]);

    $dadosPedido = [
        'cliente_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'itens' => [
            [
                'produto_id' => $produto->id,
                'quantidade' => 10, // Tentando comprar 10
                'preco_unitario_venda' => 100.00
            ]
        ]
    ];

    // 2. Act
    $response = postJson('/api/pedidos', $dadosPedido);

    // 3. Assert
    $response->assertStatus(422); // Espera um erro de validação
    $response->assertJsonValidationErrors(['itens.0.quantidade']); // Espera que o erro seja no campo da quantidade do primeiro item
});