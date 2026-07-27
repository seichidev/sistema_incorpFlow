<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acabamento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'acabavel_type',
        'acabavel_id',
        'escopo',
        'dependencia',
        'piso_revestimento',
        'piso_acabamento',
        'piso_soleira',
        'parede_revestimento',
        'parede_acabamento',
        'parede_rodape',
        'teto_revestimento',
        'teto_acabamento',
        'peitoris',
    ];

    public function acabavel(): MorphTo
    {
        return $this->morphTo();
    }
}
