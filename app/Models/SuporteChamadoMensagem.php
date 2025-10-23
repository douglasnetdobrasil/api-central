<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuporteChamadoMensagem extends Model
{
    use HasFactory;
    protected $table = 'suporte_chamado_mensagens';
    protected $guarded = ['id'];

    // --- RELACIONAMENTOS ---
    public function chamado() { return $this->belongsTo(SuporteChamado::class, 'chamado_id'); }
    public function user() { return $this->belongsTo(User::class); } // Quem respondeu (Técnico)
    public function cliente() { return $this->belongsTo(Cliente::class); } // Quem respondeu (Cliente)
    public function anexos() { return $this->hasMany(SuporteChamadoAnexo::class, 'mensagem_id'); }
}