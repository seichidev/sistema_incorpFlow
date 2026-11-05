<?php

namespace App\Models;

use App\Models\Concerns\ExcluiDefinitivamente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Construcao extends Model
{
    use ExcluiDefinitivamente;
    use SoftDeletes;

    /** Coluna do SoftDeletes (inativar). deleted_at é a exclusão definitiva. */
    const DELETED_AT = 'inactive_at';

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
    ];

    public function pavimentos(): HasMany
    {
        return $this->hasMany(Pavimento::class);
    }

    public function equipamentos(): HasMany
    {
        return $this->hasMany(Equipamento::class);
    }

    public function acabamentos(): MorphMany
    {
        return $this->morphMany(Acabamento::class, 'acabavel');
    }

    public function getCidadeEstadoAttribute(): string
    {
	return "{$this->cidade} - {$this->estado}";
    }

    protected function excluirDependentesDefinitivamente(): void
    {
        $this->pavimentos()->withTrashed()->get()->each->excluirDefinitivamente();
        $this->equipamentos()->withTrashed()->get()->each->excluirDefinitivamente();
        $this->acabamentos()->withTrashed()->get()->each->excluirDefinitivamente();
    }

    protected static function booted(): void
    {
        static::deleting(function (Construcao $construcao): void {
            if ($construcao->isForceDeleting()) {
                return;
            }

            $construcao->pavimentos()->get()->each->delete();
            $construcao->equipamentos()->get()->each->delete();
            $construcao->acabamentos()->get()->each->delete();
        });

        static::restoring(function (Construcao $construcao): void {
            $construcao->pavimentos()->onlyTrashed()->get()->each->restore();
            $construcao->equipamentos()->onlyTrashed()->get()->each->restore();
            $construcao->acabamentos()->onlyTrashed()->get()->each->restore();
        });
    }
}
