<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuporteChamadoAnexo extends Model
{
    use HasFactory;
    protected $table = 'suporte_chamado_anexos';
    protected $guarded = ['id'];

    // --- RELACIONAMENTOS ---
    public function chamado() { return $this->belongsTo(SuporteChamado::class, 'chamado_id'); }
    public function mensagem() { return $this->belongsTo(SuporteChamadoMensagem::class, 'mensagem_id'); }
}