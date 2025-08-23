<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'senha' => static::$password ??= Hash::make('password'),
            'perfil' => 'vendedor',
            'ativo' => true,
            'empresa_id' => \App\Models\Empresa::factory(),
            // As colunas 'email_verified_at' e 'remember_token' foram removidas
        ];
    }
}