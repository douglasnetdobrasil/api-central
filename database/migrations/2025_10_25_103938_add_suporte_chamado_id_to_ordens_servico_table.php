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
        Schema::table('ordens_servico', function (Blueprint $table) {
            // Adiciona a coluna
            $table->foreignId('suporte_chamado_id')
                  ->nullable() // Permite que OSs antigas ou criadas diretamente não tenham chamado de origem
                  ->after('cliente_equipamento_id'); // Posição lógica, opcional
            
            // Adiciona a chave estrangeira
            // Restrições: CASCADE em update e SET NULL em delete (o que é mais seguro para um sistema de OS)
            $table->foreign('suporte_chamado_id')
                  ->references('id')
                  ->on('suporte_chamados') // Tabela de origem
                  ->onUpdate('cascade') 
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordens_servico', function (Blueprint $table) {
            // Remove a chave estrangeira primeiro
            $table->dropConstrainedForeignId('suporte_chamado_id');

            // Remove a coluna
            $table->dropColumn('suporte_chamado_id');
        });
    }
};