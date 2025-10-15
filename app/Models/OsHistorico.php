<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsHistorico extends Model
{
    use HasFactory;

    // Define o nome da tabela para corresponder ao seu banco de dados
    protected $table = 'os_historico';

    /**
     * Define o nome da coluna 'created_at'.
     * Como sua tabela não tem 'updated_at', definimos como null para o Eloquent não procurar por ela.
     */
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'ordem_servico_id',
        'user_id',
        'descricao',
    ];

    /**
     * Relacionamento: um histórico pertence a um usuário.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relacionamento: um histórico pertence a uma Ordem de Serviço.
     */
    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}