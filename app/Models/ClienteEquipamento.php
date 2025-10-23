<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\EmpresaScope; // Importando o Scope

class ClienteEquipamento extends Model
{
    use HasFactory;

    /**
     * Nome da tabela conforme o banco
     */
    protected $table = 'cliente_equipamentos';

    /**
     * ==========================================================
     * ||||||||||||||||||| AQUI ESTÁ A CORREÇÃO |||||||||||||||||||
     * ==========================================================
     * Campos corretos, conforme a tabela 'cliente_equipamentos'
     * no seu arquivo 'central (1).sql'
     */
    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'descricao', // <-- Corrigido (era 'nome')
        'marca',       // <-- Corrigido (era 'fabricante')
        'modelo',
        'numero_serie',
        'observacoes',
    ];

    /**
     * Aplica o escopo global para filtrar por empresa_id automaticamente
     * (Igual ao seu Model OrdemServico)
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }

    /**
     * Relacionamento: O equipamento pertence a um cliente.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Relacionamento: Um equipamento pode ter muitas Ordens de Serviço.
     * Este é o relacionamento que constrói o histórico.
     */
    public function ordensServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'cliente_equipamento_id')->latest();
    }
}