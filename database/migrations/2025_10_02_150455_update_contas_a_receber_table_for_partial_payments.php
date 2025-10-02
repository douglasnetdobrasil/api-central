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
        Schema::table('contas_a_receber', function (Blueprint $table) {
            // 1. Adicionar coluna para totalizar o valor recebido
            $table->decimal('valor_recebido', 15, 2)->default(0.00)->after('valor');

            // 2. Mudar os status possíveis
            $table->enum('status', ['A Receber', 'Recebido Parcialmente', 'Recebido', 'Cancelado'])
                  ->default('A Receber')->change();

            // 3. Remover a coluna antiga de data de recebimento
            $table->dropColumn('data_recebimento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_a_receber', function (Blueprint $table) {
            // O método down deve reverter as ações do up, na ordem inversa
            
            // 3. Adicionar a coluna de volta
            $table->date('data_recebimento')->nullable()->after('valor_recebido');

            // 2. Reverter os status para o original
             $table->enum('status', ['pendente', 'recebida', 'atrasada'])
                  ->default('pendente')->change();

            // 1. Remover a coluna adicionada
            $table->dropColumn('valor_recebido');
        });
    }
};