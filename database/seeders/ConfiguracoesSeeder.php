<?php

namespace Database\Seeders;

use App\Models\Configuracao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos o método updateOrInsert para evitar criar duplicados
        // se o seeder for executado mais de uma vez. Ele verifica pela 'chave'.

        DB::table('central.configuracoes')->updateOrInsert(
            ['chave' => 'margem_lucro_padrao'], // Condição para encontrar o registo
            [   'empresa_id' => '1',
                'valor' => '100.00',
                'descricao' => 'Margem de lucro padrão (em %) aplicada a produtos sem margem definida.',
                'created_at' => now(),
                'updated_at' => now()
            ] // Dados para inserir ou atualizar
        );

        DB::table('central.configuracoes')->updateOrInsert(
            ['chave' => 'baixar_estoque_pdv'], // Condição
            [
                'empresa_id' => '1',
                'valor' => 'true',
                'descricao' => 'Baixa o estoque direto no PDV. Se "false", a baixa ocorre no faturamento.',
                'created_at' => now(),
                'updated_at' => now()
            ] // Dados
        );
    }
}
