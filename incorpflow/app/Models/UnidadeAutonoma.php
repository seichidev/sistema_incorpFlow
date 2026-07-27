<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadeAutonoma extends Model
{
    use SoftDeletes;

    protected $table = 'unidades_autonomas';

    protected $fillable = [
        'pavimento_id',
        'numero',
        'tipo',
        'permutada',
        'quantidade',
    ];

    protected $casts = [
        'permutada' => 'boolean',
    ];

    public function pavimento(): BelongsTo
    {
        return $this->belongsTo(Pavimento::class);
    }

    public function acabamentos(): MorphMany
    {
        return $this->morphMany(Acabamento::class, 'acabavel');
    }
}
