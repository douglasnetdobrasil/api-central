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
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            // O nome do campo segue a convenção do Laravel para o novo model
            $table->foreignId('categoria_conta_a_pagar_id')
                  ->nullable()
                  ->after('fornecedor_id')
                  ->constrained('categoria_contas_a_pagar') // Aponta para a nova tabela
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_a_pagar', function (Blueprint $table) {
            //
        });
    }
};
