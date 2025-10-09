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


         // =======================================================
        // 2. ADIÇÃO: CRIA O USUÁRIO SUPERVISOR
        // =======================================================
        $supervisorUser = User::firstOrCreate(
            ['email' => 'supervisor@netdobrasil.com'],
            [
                'name' => 'Supervisor Caixa',
                'password' => Hash::make('123456'),
                'pin' => '1234', // PIN padrão para o supervisor
                'empresa_id' => 1,
            ]
        );

        // Encontra o papel 'Supervisor' que foi criado no PermissionSeeder
        $supervisorRole = Role::findByName('Supervisor');

        // Atribui o papel 'Supervisor' ao novo usuário
        $supervisorUser->assignRole($supervisorRole);
    }
    }
