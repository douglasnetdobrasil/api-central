<?php

use App\Models\Cliente;
use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

test('deve ser capaz de listar clientes', function () {
    // Arrange
    Cliente::factory()->count(3)->create();

    // Act
    $response = getJson('/api/clientes');

    // Assert
    $response->assertStatus(200);
    $response->assertJsonCount(3, 'data');
});

test('deve ser capaz de criar um novo cliente', function () {
    // Arrange
    $clienteData = Cliente::factory()->make()->toArray();

    // Act
    $response = postJson('/api/clientes', $clienteData);

    // Assert
    $response->assertStatus(201);
    $response->assertJsonFragment(['nome' => $clienteData['nome']]);
    $this->assertDatabaseHas('clientes', ['cpf_cnpj' => $clienteData['cpf_cnpj']]);
});

test('deve ser capaz de exibir um cliente específico', function () {
    // Arrange
    $cliente = Cliente::factory()->create();

    // Act
    $response = getJson('/api/clientes/' . $cliente->id);

    // Assert
    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $cliente->id]);
});

test('deve ser capaz de atualizar um cliente', function () {
    // Arrange
    $cliente = Cliente::factory()->create();
    $updateData = ['nome' => 'Nome Cliente Atualizado'];

    // Act
    $response = putJson('/api/clientes/' . $cliente->id, $updateData);

    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'nome' => 'Nome Cliente Atualizado']);
});

test('deve ser capaz de deletar um cliente', function () {
    // Arrange
    $cliente = Cliente::factory()->create();

    // Act
    $response = deleteJson('/api/clientes/' . $cliente->id);

    // Assert
    $response->assertStatus(204);
    $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
});