<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasCentroCusto;

class ContaAPagar extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao model.
     */
   
    protected $table = 'contas_a_pagar';

    /**
     * Desativa a proteção de mass assignment para facilitar a gravação de dados.
     */
    protected $guarded = [];

    /**
     * Relação: Uma conta a pagar pertence a um Fornecedor.
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    /**
     * Relação: Uma conta a pagar pode ter origem numa Compra.
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }

    /**
     * Relação: Uma conta a pagar pode ter uma Forma de Pagamento.
     */
    public function formaPagamento(): BelongsTo
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    /**
     * Relação: Uma conta a pagar pertence a uma Empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function parent(): BelongsTo
    {
        // Auto-relacionamento
        return $this->belongsTo(CategoriaContaAPagar::class, 'parent_id');
    }

    public function children(): HasMany
    {
        // Auto-relacionamento
        return $this->hasMany(CategoriaContaAPagar::class, 'parent_id');
    }

    public function categoriaContaAPagar(): BelongsTo
{
    // O nome do método é o singular do nome do model
    return $this->belongsTo(CategoriaContaAPagar::class);
}
public function pagamentos()
{
    // ATUALIZE AQUI para apontar para o novo Model
    return $this->hasMany(ContaPagamento::class, 'conta_a_pagar_id');
}
}