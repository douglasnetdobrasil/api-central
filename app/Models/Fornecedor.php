<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EmpresaScope;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';

    /**
     * A lista de campos que podem ser preenchidos em massa.
     * ESTA ERA A PEÇA FALTANTE.
     */
    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'tipo_pessoa',
        'cpf_cnpj',
        'email',
        'telefone',
        'endereco',
        'ativo',
        'empresa_id'
    ];

    // Seus relacionamentos futuros virão aqui...
    // public function produtos() { ... }

    /*
    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }
    */
    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function cotacoes()
{
    return $this->belongsToMany(Cotacao::class, 'cotacao_fornecedor');
}

}
