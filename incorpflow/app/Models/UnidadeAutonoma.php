<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadeAutonoma extends Model
{
    use SoftDeletes;

    protected $table = 'unidades_autonomas';

    protected $fillable = [
        'pavimento_id',
        'numero',
        'tipo',
        'torre',
        'permutada',
    ];

    protected $casts = [
        'permutada' => 'boolean',
    ];

    public function pavimento(): BelongsTo
    {
        return $this->belongsTo(Pavimento::class);
    }
}
