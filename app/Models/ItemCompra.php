<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCompra extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     * @var string
     */
    protected $table = 'itens_compra';

    /**
     * Indica se o model deve ter timestamps (created_at e updated_at).
     * Nossa migration não criou esses campos para esta tabela.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
   
    protected $fillable = [
        'compra_id',
        'produto_id',
        'descricao_item_nota',
        'ncm',
        'cfop',
        'ean',
        'quantidade',
        'preco_custo_nota',
        'preco_entrada',
        'subtotal',
        'valor_frete',
        'valor_ipi',
        'valor_icms',
        'valor_pis',
        'valor_cofins',
        'total_item',
    ];
    /**
     * Os atributos que devem ter seu tipo convertido para tipos nativos.
     */
    protected $casts = [
        'quantidade' => 'decimal:3',
        'preco_custo_nota' => 'decimal:4',
        'subtotal' => 'decimal:2',
    ];


    // --- RELACIONAMENTOS ---

    /**
     * Um Item de Compra PERTENCE A uma Compra.
     */
    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    /**
     * Um Item de Compra PERTENCE A um Produto (após o vínculo).
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class,'produto_id');
    }

    public function produtoVinculado(): BelongsTo
    {
        // Altere 'produto_id' para o nome correto da coluna
        // em sua tabela 'itens_compra' que referencia o produto.
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}