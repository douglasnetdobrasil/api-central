<?php

use App\Models\CategoriaProduto;
use App\Models\Fornecedor;
use App\Models\UnidadeMedida;
use function Pest\Laravel\{postJson, getJson,putJson,deleteJson};
use App\Models\Produto;                  // <-- LINHA ADICIONADA/NECESSÁRIA
use App\Models\DetalheItemMercado;


test('deve ser capaz de criar um novo produto de mercado', function () {

    // 1. PREPARAÇÃO (Arrange): Criamos os dados necessários para o teste
    $unidade = UnidadeMedida::factory()->create();
    $categoria = CategoriaProduto::factory()->create();
    $fornecedor = Fornecedor::factory()->create();

    $dadosProduto = [
        "nome" => "Refrigerante 2L",
        "preco_venda" => 9.99,
        "detalhes" => [
            "marca" => "Marca Teste",
            "estoque_atual" => 100,
            "unidade_medida_id" => $unidade->id,
            "categoria_id" => $categoria->id,
            "fornecedor_id" => $fornecedor->id
        ],
        "dados_fiscais" => [
            "ncm" => "22021000",
            "origem" => "0"
        ]
    ];

    // 2. AÇÃO (Act): Fazemos a chamada para a API
    $response = postJson('/api/produtos/mercado', $dadosProduto);

    // 3. VERIFICAÇÃO (Assert): Verificamos se tudo aconteceu como esperado
    $response->assertStatus(201);
    $response->assertJsonFragment(['nome' => 'Refrigerante 2L']);
    $this->assertDatabaseHas('produtos', ['nome' => 'Refrigerante 2L']);
    $this->assertDatabaseHas('detalhes_item_mercado', ['marca' => 'Marca Teste']);
    $this->assertDatabaseHas('dados_fiscais_produto', ['ncm' => '22021000']);


      


});

// NOVO TESTE PARA EXIBIR UM PRODUTO
test('deve ser capaz de exibir um produto de mercado específico', function() {
    // 1. PREPARAÇÃO (Arrange)
    // Cria um produto de mercado para podermos buscá-lo
    $detalhe = DetalheItemMercado::factory()->create();
    $produto = $detalhe->produto()->create(['nome' => 'Produto Específico', 'preco_venda' => 25.00]);

    // 2. AÇÃO (Act)
    // Faz a requisição para a URL do produto específico
    $response = getJson('/api/produtos/mercado/' . $produto->id);

    // 3. VERIFICAÇÃO (Assert)
    $response->assertStatus(200); // Verifica se a requisição foi um sucesso
    $response->assertJsonFragment(['nome' => 'Produto Específico']); // Verifica se o nome do produto está na resposta
});




// ...

// NOVO TESTE PARA ATUALIZAR UM PRODUTO
test('deve ser capaz de atualizar um produto de mercado', function() {
    // 1. Arrange: Cria um produto inicial
    $detalhe = DetalheItemMercado::factory()->create(['marca' => 'Marca Antiga']);
    $produto = $detalhe->produto()->create(['nome' => 'Nome Antigo', 'preco_venda' => 10.00]);

    // 2. Act: Prepara os dados da atualização e faz a chamada
    $dadosUpdate = [
        'nome' => 'Nome Novo e Atualizado',
        'detalhes' => [
            'marca' => 'Marca Nova'
        ]
    ];
    $response = putJson('/api/produtos/mercado/' . $produto->id, $dadosUpdate);

    // 3. Assert: Verifica se tudo foi atualizado corretamente
    $response->assertStatus(200);
    $response->assertJsonFragment(['nome' => 'Nome Novo e Atualizado']);

    $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'nome' => 'Nome Novo e Atualizado']);
    $this->assertDatabaseHas('detalhes_item_mercado', ['id' => $detalhe->id, 'marca' => 'Marca Nova']);
});

test('deve ser capaz de deletar um produto de mercado', function() {
    // 1. Arrange: Cria um produto completo para deletar
    $detalhe = DetalheItemMercado::factory()->create();
    $produto = $detalhe->produto()->create(['nome' => 'Produto a ser Deletado', 'preco_venda' => 10.00]);
    $dadoFiscal = $produto->dadoFiscal()->create(['ncm' => '12345678']);

    // 2. Act: Faz a chamada para o endpoint de exclusão
    $response = deleteJson('/api/produtos/mercado/' . $produto->id);

    // 3. Assert: Verifica se a operação foi um sucesso e se os dados sumiram do banco
    $response->assertStatus(204); // Verifica se a resposta foi "No Content"

    $this->assertDatabaseMissing('produtos', ['id' => $produto->id]);
    $this->assertDatabaseMissing('detalhes_item_mercado', ['id' => $detalhe->id]);
    $this->assertDatabaseMissing('dados_fiscais_produto', ['id' => $dadoFiscal->id]);
});