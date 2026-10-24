<?php

namespace App\Models;

use App\Models\Concerns\ExcluiDefinitivamente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipamento extends Model
{
    use ExcluiDefinitivamente;
    use SoftDeletes;

    protected $fillable = [
        'construcao_id',
        'nome',
        'tipo_ou_marca',
        'acabamento',
        'detalhes_gerais',
    ];

    public function construcao(): BelongsTo
    {
        return $this->belongsTo(Construcao::class);
    }
}
