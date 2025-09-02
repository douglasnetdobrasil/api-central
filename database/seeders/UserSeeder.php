<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Importa o modelo User
use Spatie\Permission\Models\Role; // Importa o modelo Role
use Illuminate\Support\Facades\Hash; // Importa o Hash para a senha
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cria o usuário Administrador padrão
        $adminUser = User::create([
            'name' => 'netdobrasil',
            'email' => 'netdobrasil@netdobrasil.com',
            'empresa_id' => '1',
            'password' => Hash::make('netdobrasil'), // Senha padrão é 'password'
        ]);

        // Encontra o perfil 'Admin' que foi criado no PermissionSeeder
        $adminRole = Role::findByName('Admin');

        // Atribui o perfil 'Admin' ao usuário recém-criado
        $adminUser->assignRole($adminRole);
    }
}