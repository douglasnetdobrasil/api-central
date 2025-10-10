<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caixa extends Model
{
    use HasFactory;

    /**
     * A tabela associada a este model.
     *
     * @var string
     */
    protected $table = 'caixas';

    /**
     * Os atributos que podem ser preenchidos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'empresa_id',
        'user_id',
        'terminal_id',
        'status',
        'valor_abertura',
        'valor_fechamento',
        'data_abertura',
        'data_fechamento',
    ];

    protected $casts = [
        'data_abertura' => 'datetime',
        'data_fechamento' => 'datetime',
    ];

    /**
     * Define a relação: Uma sessão de Caixa PERTENCE A um Usuário.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Define a relação: Uma sessão de Caixa PERTENCE A uma Empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function vendas()
{
    return $this->hasMany(Venda::class);
}

    /**
     * Define a relação: Uma sessão de Caixa TEM MUITAS Vendas.
     */
    
}