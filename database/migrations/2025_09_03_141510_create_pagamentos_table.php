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
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->string('forma_pagamento'); // Ex: dinheiro, pix, cartao_credito
            $table->decimal('valor', 10, 2);
            $table->integer('parcelas')->default(1);
            $table->json('detalhes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};