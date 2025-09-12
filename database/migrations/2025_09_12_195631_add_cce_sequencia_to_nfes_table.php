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
        Schema::table('nfes', function (Blueprint $table) {
            // A primeira CC-e sempre terá a sequência 1.
            $table->tinyInteger('cce_sequencia_evento')->default(1)->after('protocolo_autorizacao');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfes', function (Blueprint $table) {
            //
        });
    }
};
