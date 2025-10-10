<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{




   
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
       
       // User::factory()->create([
       //     'name' => 'Test User',
       //     'email' => 'netdobrasil@netdobrasil.com.br',
       //     'empresa_id' => '1',
        //     'password' => 'netdobrasil'

        //]);

        $this->call([
            EmpresaSeeder::class,
            ConfiguracoesSeeder::class,
            CategoriaProdutoSeeder::class,
            UnidadeDeMedidaSeeder::class,
            FormaPagamentoSeeder::class,
            NaturezaOperacaoSeeder::class,
            PermissaoCaixaSeeder::class,
            TerminalSeeder::class
            
            // ...seus outros seeders aqui
        ]);

        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
            // Outros seeders que você tenha...
        ]);


        
    }

    

   
}


