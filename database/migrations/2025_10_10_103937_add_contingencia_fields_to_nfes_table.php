<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // O método up() é executado quando você roda o comando "php artisan migrate"
        Schema::table('nfes', function (Blueprint $table) {
            // Adiciona a coluna para a justificativa de entrada em contingência
            $table->text('justificativa_contingencia')
                  ->nullable()
                  ->after('justificativa_cancelamento');

            // Adiciona a coluna para guardar o motivo de rejeição, caso ocorra no envio
            $table->text('motivo_rejeicao')
                  ->nullable()
                  ->after('mensagem_erro');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // O método down() é executado quando você precisa reverter a migration
        Schema::table('nfes', function (Blueprint $table) {
            $table->dropColumn(['justificativa_contingencia', 'motivo_rejeicao']);
        });
    }
};