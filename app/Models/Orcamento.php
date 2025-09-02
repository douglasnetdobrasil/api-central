<?php

namespace App\Models;

use App\Models\Cliente;
use App\Models\OrcamentoItem;
use App\Models\Scopes\EmpresaScope;
use App\Models\User; // Importe o modelo User
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orcamento extends Model
{
    use HasFactory;

    // Campos baseados na sua migração
    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'vendedor_id', // Adicionado
        'status',
        'data_emissao',
        'data_validade',
        'valor_total',
        'observacoes',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_validade' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }

    // --- RELACIONAMENTOS ---

    public function items()
    {
        return $this->hasMany(OrcamentoItem::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relacionamento com o vendedor (User)
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }
}