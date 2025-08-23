<?php

namespace Database\Seeders;

use App\Models\Configuracao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfiguracoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos updateOrCreate para evitar duplicatas se o seeder for rodado mais de uma vez
        Configuracao::updateOrCreate(
            ['chave' => 'margem_lucro_padrao'], // Condição para encontrar
            [
                'valor' => '100.00', // Valor a ser inserido/atualizado
                'descricao' => 'Margem de lucro padrão (em %) aplicada a produtos que não possuem margem própria ou de categoria.'
            ]
        );
    }
}