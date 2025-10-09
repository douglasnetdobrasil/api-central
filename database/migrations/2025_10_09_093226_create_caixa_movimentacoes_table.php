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
        Schema::create('caixa_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixa_id')->constrained('caixas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->comment('Operador que realizou a ação');
            // Futuramente, podemos adicionar um campo para o supervisor que autorizou
            // $table->foreignId('supervisor_id')->nullable()->constrained('users');
            $table->enum('tipo', ['SANGRIA', 'SUPRIMENTO']);
            $table->decimal('valor', 10, 2);
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caixa_movimentacoes');
    }
};