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
        Schema::table('produtos', function (Blueprint $table) {
            // Adiciona a nova coluna depois da coluna 'categoria_id' para organização
            $table->foreignId('setor_id')
                  ->nullable()
                  ->after('categoria_id')
                  ->constrained('setores')
                  ->onDelete('set null');
        });
    }
    
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna, tornando a migration reversível
            $table->dropForeign(['setor_id']);
            $table->dropColumn('setor_id');
        });
    }
};
