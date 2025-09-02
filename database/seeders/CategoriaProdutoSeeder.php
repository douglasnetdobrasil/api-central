<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaProdutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   
        // Insere um conjunto de categorias padrão
        public function run(): void
        {
            // Insere a categoria padrão do sistema, associada à empresa com ID 1
            DB::table('categorias')->insert([
                'nome' => 'Padrão',
                'empresa_id' => 1, // Associa à empresa padrão
                'margem_lucro' => 100
            ]);
    
            // Você pode adicionar outras categorias aqui se quiser,
            // sempre especificando o empresa_id
            /*
            DB::table('categorias_produto')->insert([
                ['nome' => 'Mercearia', 'empresa_id' => 1],
                ['nome' => 'Bebidas', 'empresa_id' => 1],
            ]);
            */
        }
}
