<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Configuracao; // Importa o seu modelo

class ConfiguracaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cria ou atualiza a configuração da categoria padrão
        Configuracao::updateOrCreate(
            ['chave' => 'categoria_padrao_id'],
            [
                // IMPORTANTE: Troque '1' pelo ID de uma categoria real que exista
                // na sua tabela 'categorias' (ex: "GERAL", "DIVERSOS", etc.)
                'valor' => '1' 
            ]
        );

        // Aproveitamos e criamos também a margem de lucro padrão, que o seu código já usa
        Configuracao::updateOrCreate(
            ['chave' => 'margem_lucro_padrao'],
            ['valor' => '30'] // Exemplo: 30% de margem padrão. Ajuste se necessário.
        );
    }
}