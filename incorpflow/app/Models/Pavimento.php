<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pavimento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'construcao_id',
        'nome',
        'tipo',
        'nivel',
        'quantidade',
        'torre',
        'area_construida',
    ];

    protected $casts = [
        'area_construida' => 'decimal:4',
    ];

    public function construcao(): BelongsTo
    {
        return $this->belongsTo(Construcao::class);
    }

    public function unidadesAutonomas(): HasMany
    {
        return $this->hasMany(UnidadeAutonoma::class);
    }

    /**
     * Composição: as unidades autônomas acompanham o pavimento na exclusão
     * e na restauração.
     */
    protected static function booted(): void
    {
        static::deleting(function (Pavimento $pavimento): void {
            if ($pavimento->isForceDeleting()) {
                return;
            }

            $pavimento->unidadesAutonomas()->get()->each->delete();
        });

        static::restoring(function (Pavimento $pavimento): void {
            $pavimento->unidadesAutonomas()->onlyTrashed()->get()->each->restore();
        });
    }
}
