<?php

use App\Models\Fornecedor;
use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};
use App\Models\Produto; // Adicione este 'use' no topo do arquivo
use App\Models\DetalheItemMercado;

test('deve ser capaz de listar fornecedores', function () {
    // Arrange: Cria 3 fornecedores de teste
    Fornecedor::factory()->count(3)->create();

    // Act: Faz a chamada para a API
    $response = getJson('/api/fornecedores');

    // Assert: Verifica a resposta
    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
});

// Dentro do arquivo tests/Feature/Api/FornecedorTest.php

test('deve ser capaz de criar um novo fornecedor', function () {
    // Arrange: Voltamos a usar a factory, que agora funciona!
    $fornecedorData = Fornecedor::factory()->make()->toArray();

    // Act: Faz a chamada para a API
    $response = postJson('/api/fornecedores', $fornecedorData);

    // Assert: Verifica a resposta e o banco de dados
    $response->assertStatus(201);
    $response->assertJsonFragment(['razao_social' => $fornecedorData['razao_social']]);
    $this->assertDatabaseHas('fornecedores', ['cpf_cnpj' => $fornecedorData['cpf_cnpj']]);
});
test('deve ser capaz de exibir um fornecedor específico', function () {
    // Arrange: Cria um fornecedor
    $fornecedor = Fornecedor::factory()->create();

    // Act: Faz a chamada para a API buscando pelo ID
    $response = getJson('/api/fornecedores/' . $fornecedor->id);

    // Assert: Verifica a resposta
    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $fornecedor->id]);
});

test('deve ser capaz de atualizar um fornecedor', function () {
    // Arrange: Cria um fornecedor e prepara os dados para atualização
    $fornecedor = Fornecedor::factory()->create();
    $updateData = ['razao_social' => 'Nova Razão Social Atualizada'];

    // Act: Faz a chamada para a API
    $response = putJson('/api/fornecedores/' . $fornecedor->id, $updateData);

    // Assert: Verifica a resposta e o banco de dados
    $response->assertStatus(200);
    $this->assertDatabaseHas('fornecedores', ['id' => $fornecedor->id, 'razao_social' => 'Nova Razão Social Atualizada']);
});

test('deve ser capaz de deletar um fornecedor sem vínculos', function () {
    // Arrange: Cria um fornecedor sem produtos ou contas
    $fornecedor = Fornecedor::factory()->create();

    // Act: Faz a chamada para a API
    $response = deleteJson('/api/fornecedores/' . $fornecedor->id);

    // Assert: Verifica se a deleção foi um sucesso
    $response->assertStatus(204);
    $this->assertDatabaseMissing('fornecedores', ['id' => $fornecedor->id]);
});

test('não deve ser capaz de deletar um fornecedor com produtos vinculados', function () {
    // Arrange: Cria um fornecedor e um produto associado a ele
    $fornecedor = Fornecedor::factory()->create();
    $detalhe = DetalheItemMercado::factory()->create(['fornecedor_id' => $fornecedor->id]);
    $detalhe->produto()->create(['nome' => 'Produto Vinculado', 'preco_venda' => 10]);

    // Act: Tenta deletar o fornecedor
    $response = deleteJson('/api/fornecedores/' . $fornecedor->id);

    // Assert: Verifica se a API retornou o erro de conflito esperado
    $response->assertStatus(409);
    $this->assertDatabaseHas('fornecedores', ['id' => $fornecedor->id]);
});