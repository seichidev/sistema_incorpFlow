<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
	'data_inicio',
	'data_previsao_conclusao',
    ];

    public function getCidadeEstadoAttribute(): string
    {
	return "{$this->cidade} - {$this->estado}";
    }

//  public function pavimentos(): Has Many
//  {
//    return $this->hasMany(Pavimento::class, 'construcao_id');
//  }

//  public function unidade(): HasMany
//  {
//    return $this->hasMany(Unidade::class, 'construcao_id');
//  }

//  public function getTotalPavimentosAttribute() : int
//  {
//    return $this->pavimentos()->count();
//  }

//  public function getTotalUnidadesAttribute() : int
//  {
//    return $this->unidades()->count();
//  }
    
}
