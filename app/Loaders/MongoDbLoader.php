<?php

namespace App\Loaders; // <-- O namespace correto

use App\Models\Projeto;
use MongoDB\Client; // <-- Usa o cliente do MongoDB

/**
 * Classe responsável por CARREGAR (Load) dados no MongoDB.
 */
class MongoDbLoader
{
    protected Client $mongoClient;
    protected string $databaseName;

    /**
     * Estabelece a conexão com o MongoDB de destino.
     */
    public function __construct(Projeto $projeto, string $tipo = 'destino')
    {
        // Busca a conexão do tipo 'mongodb' de destino
        //
        $conexao = $projeto->conexoes
            ->where('tipo', $tipo)
            ->where('driver', 'mongodb')
            ->first();

        if (!$conexao) {
            throw new \Exception("Configuração de conexão MongoDB de {$tipo} não encontrada.");
        }

        // Usa o campo 'host' para a string de conexão
        $connectionString = $conexao->host;
        $this->databaseName = $conexao->banco; //

        // Conecta ao cliente
        $this->mongoClient = new Client($connectionString);
    }

    /**
     * Insere um lote (chunk) de dados em uma coleção do MongoDB.
     *
     * @param string $collectionName O nome da coleção (tabela) de destino.
     * @param array $dataToInsert O array de documentos (linhas) a serem inseridos.
     */
    public function loadData(string $collectionName, array $dataToInsert): void
    {
        // Se o array estiver vazio, não faz nada
        if (empty($dataToInsert)) {
            return;
        }

        try {
            // 1. Seleciona o banco de dados
            $database = $this->mongoClient->selectDatabase($this->databaseName);

            // 2. Seleciona a coleção
            $collection = $database->selectCollection($collectionName);

            // 3. Insere todos os documentos do lote de uma vez (muito eficiente)
            $collection->insertMany($dataToInsert);

        } catch (\Exception $e) {
            // Se falhar, lança a exceção para o Job poder capturar
            throw new \Exception("Falha ao carregar dados no MongoDB: " . $e->getMessage());
        }
    }
}