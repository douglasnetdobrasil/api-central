<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\EmpresaScope;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'cliente_id',
        'orcamento_id',
        'subtotal',
        'desconto',
        'total',
        'status',
        'observacoes',
        'nfe_chave_acesso',
    ];

    // --- RELACIONAMENTOS ---

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Uma Venda TEM MUITOS Itens.
     */
    public function items(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    /**
     * Uma Venda TEM MUITOS Pagamentos.
     * ESTA É A CORREÇÃO PRINCIPAL: Apontando para o modelo correto 'VendaPagamento'.
     */
    public function pagamentos(): HasMany
    {
        return $this->hasMany(VendaPagamento::class);
    }
    
    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }
}