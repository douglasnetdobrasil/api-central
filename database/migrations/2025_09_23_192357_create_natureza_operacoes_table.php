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
        Schema::create('natureza_operacoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao'); // Ex: "Venda de Mercadoria"
            $table->string('cfop', 4); // Ex: "5102"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('natureza_operacoes');
    }
};
