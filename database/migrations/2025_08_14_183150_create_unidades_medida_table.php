<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100)->unique();
            $table->string('sigla', 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
};