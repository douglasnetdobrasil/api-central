<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setor extends Model
{
    use HasFactory;
    protected $table = 'setores';

    protected $fillable = ['nome', 'empresa_id'];

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }
}