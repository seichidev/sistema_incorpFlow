<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Construcao extends Model
{
    use SoftDeletes;

    protected $table = 'construcoes';

    protected $fillable = [
	'nome',
	'endereco',
	'cidade',
	'estado',
	'tipo',
	'status',
	'responsavel',
	'descricao',
	'unidades',
    ];

    public function pavimentos(): HasMany
    {
        return $this->hasMany(Pavimento::class);
    }

    public function getCidadeEstadoAttribute(): string
    {
	return "{$this->cidade} - {$this->estado}";
    }
}
