<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\EmpresaScope;
use App\Models\OsProduto;
use App\Models\OsServico;
use App\Models\OsFoto;


class OrdemServico extends Model
{
    use HasFactory;

    /**
     * Força o Eloquent a usar o nome de tabela correto que criamos na migration.
     */
    protected $table = 'ordens_servico';

    /**
     * AQUI ESTÁ A CORREÇÃO PRINCIPAL:
     * Lista de todos os campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'tecnico_id',
        'status',
        'data_entrada',
        'data_previsao_conclusao',
        'data_conclusao',
        'equipamento',
        'numero_serie',
        'defeito_relatado',
        'laudo_tecnico',
        'garantia',
        'valor_servicos',
        'valor_produtos',
        'valor_desconto',
        'valor_total',
        'venda_id',
        'cliente_equipamento_id', 
        'suporte_chamado_id',
    ];

    protected $casts = [
        'data_entrada' => 'datetime',
        'data_previsao_conclusao' => 'date',
        'data_conclusao' => 'datetime',
        'valor_servicos' => 'decimal:2',
        'valor_produtos' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    /**
     * Aplica o escopo global para filtrar por empresa_id automaticamente.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }

    // --- RELACIONAMENTOS ---

    public function empresa(): BelongsTo { return $this->belongsTo(Empresa::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class, 'cliente_id'); }
    public function tecnico(): BelongsTo { return $this->belongsTo(User::class, 'tecnico_id'); }
    public function venda(): BelongsTo { return $this->belongsTo(Venda::class, 'venda_id'); }
    public function produtos(): HasMany { return $this->hasMany(OsProduto::class, 'ordem_servico_id'); }
    public function servicos(): HasMany { return $this->hasMany(OsServico::class, 'ordem_servico_id'); }
    public function historico(): HasMany { return $this->hasMany(OsHistorico::class, 'ordem_servico_id')->latest(); }

    /**
     * Recalcula e salva os totais da OS com base nos seus itens.
     */
    public function atualizarValores(): void
    {
        $this->valor_produtos = $this->produtos()->sum('subtotal');
        $this->valor_servicos = $this->servicos()->sum('subtotal');
        $totalBruto = $this->valor_produtos + $this->valor_servicos;
        $this->valor_total = $totalBruto - $this->valor_desconto;
        $this->save();
    }

    // >> NOVO RELACIONAMENTO: Equipamento
    public function equipamento(): BelongsTo
    {
        return $this->belongsTo(ClienteEquipamento::class, 'cliente_equipamento_id');
    }
    public function chamado(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SuporteChamado::class, 'suporte_chamado_id');
    }
    public function chamadoDeOrigem(): BelongsTo
    {
        // Garante que o Laravel saiba onde encontrar a classe,
        // mesmo que haja conflito de nomes ou falta de 'use'.
        return $this->belongsTo(\App\Models\SuporteChamado::class, 'suporte_chamado_id');
    }
    // >> NOVO RELACIONAMENTO: Fotos
    public function fotos(): HasMany
    {
        return $this->hasMany(OsFoto::class, 'ordem_servico_id')->oldest();
    }
}