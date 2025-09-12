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
        Schema::create('cces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfe_id')->constrained('nfes')->onDelete('cascade');
            $table->integer('sequencia_evento');
            $table->string('caminho_xml');
            $table->string('caminho_pdf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cces');
    }
};
