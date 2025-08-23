<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoEstoque extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     * @var string
     */
    protected $table = 'movimentacoes_estoque';

    /**
     * Indica se o model deve ter timestamps (created_at e updated_at).
     * Nossa migration tem um campo customizado 'data_movimentacao', então desativamos os padrões.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'produto_id',
        'tipo',
        'quantidade',
        'estoque_anterior',
        'estoque_novo',
        'origem_documento',
        'origem_id',
        'usuario_id',
        'data_movimentacao',
    ];

    /**
     * Os atributos que devem ter seu tipo convertido para tipos nativos.
     */
    protected $casts = [
        'quantidade' => 'decimal:3',
        'estoque_anterior' => 'decimal:3',
        'estoque_novo' => 'decimal:3',
        'data_movimentacao' => 'datetime',
    ];


    // --- RELACIONAMENTOS ---

    /**
     * Uma movimentação PERTENCE A um Produto.
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * Uma movimentação foi realizada por um Usuário.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Uma movimentação TEM UMA origem, que pode ser de vários tipos (polimórfico).
     */
    public function origem()
    {
        // Esta é a mágica do relacionamento polimórfico
        return $this->morphTo();
    }
}