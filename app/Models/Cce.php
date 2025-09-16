<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Cce extends Model
{
    use HasFactory;
    protected $fillable = ['nfe_id', 'sequencia_evento', 'caminho_xml', 'caminho_pdf'];
}
