<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuporteChamado extends Model
{
    use HasFactory;
    protected $table = 'suporte_chamados';
    protected $guarded = ['id']; // Permite preencher tudo, exceto ID

    protected $casts = [
        'data_resolucao' => 'datetime',
        'data_fechamento' => 'datetime',
    ];

    // Gera um protocolo único ao criar um novo chamado
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($chamado) {
            $data = now()->format('Ym'); // Ex: 202510
            $ultimoId = static::max('id') + 1;
            $chamado->protocolo = $data . '-' . str_pad($ultimoId, 4, '0', STR_PAD_LEFT);
        });
    }

    // --- RELACIONAMENTOS ---
    public function empresa() { return $this->belongsTo(Empresa::class); }
    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function equipamento() { return $this->belongsTo(ClienteEquipamento::class, 'cliente_equipamento_id'); }
    public function tecnico() { return $this->belongsTo(User::class, 'tecnico_atribuido_id'); }
    public function ordemServico() { return $this->belongsTo(OrdemServico::class); }

    // A "Timeline"
    public function mensagens() { return $this->hasMany(SuporteChamadoMensagem::class, 'chamado_id')->oldest(); }
    public function anexos() { return $this->hasMany(SuporteChamadoAnexo::class, 'chamado_id'); }
}