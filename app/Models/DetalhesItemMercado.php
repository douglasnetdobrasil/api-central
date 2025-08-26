<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalhesItemMercado extends Model
{
    use HasFactory;

    protected $table = 'detalhes_item_mercado';
    public $timestamps = false;

    // GARANTA QUE TODOS OS CAMPOS DA SUA REQUEST ESTEJAM AQUI
    protected $fillable = [
        'marca',
        'codigo_barras',
        'categoria_id',
        'fornecedor_id',
        'preco_custo',
        'preco_promocional',
        'data_inicio_promocao',
        'data_fim_promocao',
        'estoque_atual',
        'estoque_minimo',
        'unidade_medida_id',
        'controla_validade',
        'vendido_por_peso',
    ];

    public function produto()
    {
        return $this->morphOne(Produto::class, 'detalhe');
    }
}