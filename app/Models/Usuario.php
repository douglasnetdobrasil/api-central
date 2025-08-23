<?php

namespace App\Models;

// 1. IMPORTA O TRAIT NECESSÁRIO
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    // 2. "USA" O TRAIT DENTRO DA CLASSE
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'empresa_id',
        'nome',
        'email',
        'senha',
        'perfil',
        'ativo',
    ];

    /**
     * Atributos que devem ser ocultados ao serializar.
     */
    protected $hidden = [
        'senha',
        'remember_token',
    ];

    /**
     * Atributos que devem ter seu tipo convertido.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'senha' => 'hashed',
        'ativo' => 'boolean',
    ];

    // --- RELACIONAMENTOS ---

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pedidosComoVendedor()
    {
        return $this->hasMany(Pedido::class, 'vendedor_id');
    }

    public function historicoAcoes()
    {
        return $this->hasMany(HistoricoPedido::class, 'usuario_id');
    }
}