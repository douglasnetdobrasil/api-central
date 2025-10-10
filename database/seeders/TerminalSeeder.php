<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Terminal;

class TerminalSeeder extends Seeder
{
    public function run(): void
    {
        Terminal::firstOrCreate(
            ['empresa_id' => 1, 'numero' => 1],
            ['descricao' => 'Caixa 01 - Frente de Loja']
        );
        Terminal::firstOrCreate(
            ['empresa_id' => 1, 'numero' => 2],
            ['descricao' => 'Caixa 02 - Balcão']
        );
        // Adicione quantos caixas precisar
    }
}