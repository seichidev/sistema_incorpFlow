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
        'tipologia',
        'permutada',
        'quantidade',
        'area_privativa',
        'area_comum',
        'area_equivalente',
    ];

    protected $casts = [
        'permutada' => 'boolean',
        'area_privativa' => 'decimal:4',
        'area_comum' => 'decimal:4',
        'area_equivalente' => 'decimal:4',
    ];

    /**
     * Separa a lista de números de unidade em itens individuais.
     *
     * Os Quadros escrevem a lista separando por vírgula e usando " e " antes
     * do último item — "1301, 1304, 1305, 1308, 1401, 1404, 1405 e 1408". A
     * vírgula nem sempre vem seguida de espaço ("1108,1201") e a lista pode
     * ser digitada em várias linhas, como aparece no Quadro IV-B.
     *
     * @return list<string>
     */
    public static function separarNumeros(?string $numero): array
    {
        if (blank($numero)) {
            return [];
        }

        $itens = preg_split('/\s*,\s*|\s+e\s+|\R/u', trim($numero));

        return array_values(array_filter(
            array_map('trim', $itens ?: []),
            fn (string $item): bool => $item !== '',
        ));
    }

    /**
     * Números repetidos dentro da lista — uma unidade não pode ser declarada
     * duas vezes na mesma tipologia.
     *
     * @return list<string>
     */
    public static function numerosDuplicados(?string $numero): array
    {
        $numeros = self::separarNumeros($numero);

        return array_values(array_unique(
            array_diff_assoc($numeros, array_unique($numeros))
        ));
    }

    /**
     * Memo de [numero, contagem] — as colunas da tabela consultam a contagem
     * várias vezes por linha (cor, ícone e tooltip), e a lista pode ter
     * centenas de caracteres. Guardar o número junto invalida o memo sozinho
     * quando o valor muda.
     *
     * @var array{0: ?string, 1: int}|null
     */
    private ?array $memoNumeros = null;

    /**
     * Quantidade de unidades efetivamente listadas em `numero`.
     *
     * Deve bater com `quantidade`, que é o multiplicador das áreas em todo o
     * cálculo do orçamento — a soma de (area_equivalente x quantidade) é o
     * denominador do coeficiente de proporcionalidade de todas as unidades.
     */
    public function getQuantidadeListadaAttribute(): int
    {
        $numero = $this->numero;

        if ($this->memoNumeros === null || $this->memoNumeros[0] !== $numero) {
            $this->memoNumeros = [$numero, count(self::separarNumeros($numero))];
        }

        return $this->memoNumeros[1];
    }

    public function getQuantidadeConfereAttribute(): bool
    {
        return $this->quantidade_listada === (int) $this->quantidade;
    }

    public function pavimento(): BelongsTo
    {
        return $this->belongsTo(Pavimento::class);
    }

    public function acabamentos(): MorphMany
    {
        return $this->morphMany(Acabamento::class, 'acabavel');
    }
}
