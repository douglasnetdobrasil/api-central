<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoFiscalPadrao extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'configuracoes_fiscais_padrao';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'empresa_id',
        'nome_perfil',
        'ncm_padrao',
        'cfop_padrao',
        'origem_padrao',
        'csosn_padrao',
        'icms_cst_padrao',
        'pis_cst_padrao',
        'cofins_cst_padrao',
    ];
}