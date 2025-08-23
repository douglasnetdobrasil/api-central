<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_a_pagar', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['pendente', 'paga', 'atrasada'])->default('pendente');
            $table->timestamps();

            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_a_pagar');
    }
};