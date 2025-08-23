<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('itens_compra', function (Blueprint $table) {
            $table->string('ncm', 10)->nullable()->after('descricao_item_nota');
            $table->string('cfop', 5)->nullable()->after('ncm');
            $table->string('ean', 20)->nullable()->after('cfop');
        });
    }

    public function down(): void
    {
        Schema::table('itens_compra', function (Blueprint $table) {
            $table->dropColumn(['ncm', 'cfop', 'ean']);
        });
    }
};