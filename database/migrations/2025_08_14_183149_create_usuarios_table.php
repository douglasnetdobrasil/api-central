<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('senha'); // Armazenar sempre a senha com hash (ex: bcrypt)
            $table->enum('perfil', ['admin', 'vendedor', 'estoquista'])->default('vendedor');
            $table->boolean('ativo')->default(true);
            $table->timestamps(); // Cria as colunas `criado_em` e `atualizado_em`
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};