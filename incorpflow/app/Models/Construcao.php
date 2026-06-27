<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
	'pavimentos',
	'unidades',
    ];

    public function getCidadeEstadoAttribute(): string
    {
	return "{$this->cidade} - {$this->estado}";
    }    
}
