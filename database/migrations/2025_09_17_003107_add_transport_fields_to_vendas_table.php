<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Adiciona campos de transporte após a coluna 'cliente_id'
            $table->after('cliente_id', function ($table) {
                $table->foreignId('transportadora_id')->nullable()->constrained('transportadoras');
                $table->integer('frete_modalidade')->default(9);
                $table->decimal('frete_valor', 10, 2)->default(0);
                $table->decimal('peso_bruto', 10, 3)->nullable();
                $table->decimal('peso_liquido', 10, 3)->nullable();
            });
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropForeign(['transportadora_id']);
            $table->dropColumn([
                'transportadora_id',
                'frete_modalidade',
                'frete_valor',
                'peso_bruto',
                'peso_liquido'
            ]);
        });
    }
};