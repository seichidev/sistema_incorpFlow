<?php

namespace App\Models;

use App\Models\Concerns\ExcluiDefinitivamente;
use Collator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipamento extends Model
{
    use ExcluiDefinitivamente;
    use SoftDeletes;

    /** Coluna do SoftDeletes (inativar). deleted_at é a exclusão definitiva. */
    const DELETED_AT = 'inactive_at';

    /**
     * Sugestões para o nome do equipamento. Não é lista fechada: os nomes já
     * cadastrados também são sugeridos, e qualquer texto é aceito.
     */
    public const NOMES = [
        'Elevador social', 'Elevador de serviço', 'Portão de garagem', 'Portão de pedestres',
        'Interfone', 'CFTV', 'Gerador', 'Bomba de recalque', 'Central de gás',
        'Sistema de combate a incêndio', 'Para-raios (SPDA)', 'Antena coletiva',
        'Central de ar-condicionado', 'Aquecimento central',
    ];

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

    /** @return list<string> */
    public static function sugestoesDeNome(): array
    {
        return collect(self::NOMES)->merge(static::valoresUsados('nome'))->unique()
            ->sort((new Collator('pt_BR'))->compare(...))->values()->all();
    }

    /**
     * Valores já cadastrados num campo. Com um nome informado, os de equipamentos
     * de mesmo nome vêm primeiro: num elevador, as marcas usadas em elevadores.
     *
     * @return list<string>
     */
    public static function valoresUsados(string $campo, ?string $nome = null): array
    {
        $consulta = fn () => static::withTrashed()->whereNotNull($campo)->where($campo, '!=', '');

        $mesmoNome = filled($nome)
            ? $consulta()->whereRaw('lower(nome) = lower(?)', [trim($nome)])->latest('id')->pluck($campo)
            : collect();

        return $mesmoNome->merge($consulta()->orderBy($campo)->pluck($campo))->unique()->values()->all();
    }

    /** O equipamento mais recente com este nome, sem diferenciar maiúsculas. */
    public static function referenciaPara(?string $nome): ?self
    {
        if (blank($nome)) {
            return null;
        }

        return static::withTrashed()->whereRaw('lower(nome) = lower(?)', [trim($nome)])->latest('id')->first();
    }
}
