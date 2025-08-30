<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Passo 1: Adiciona a coluna, mas permite que ela seja nula temporariamente
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('empresa_id')->after('id')->nullable()->constrained('empresas')->onDelete('cascade');
        });
    
        // Passo 2: Atualiza todos os clientes que já existem para pertencerem à empresa principal (ID 1)
        // IMPORTANTE: Se a sua empresa principal no banco tiver um ID diferente, troque o '1' abaixo.
        if (DB::table('clientes')->exists()) {
            DB::table('clientes')->update(['empresa_id' => 1]);
        }
    
        // Passo 3: Agora que todos os clientes têm um empresa_id, modifica a coluna para NÃO permitir mais valores nulos
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable(false)->change();
        });
    }
};
