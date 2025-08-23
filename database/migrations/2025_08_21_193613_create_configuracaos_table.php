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
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique(); // A "chave" da configuração, ex: 'margem_lucro_padrao'
            $table->text('valor')->nullable(); // O "valor" da configuração, ex: '100.00'
            $table->string('descricao')->nullable(); // Uma breve descrição do que a configuração faz
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};