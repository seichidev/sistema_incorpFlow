<?php

namespace App\Rules;

use App\Models\UnidadeAutonoma;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Garante que a quantidade informada bata com a lista de números da unidade.
 *
 * A soma de (area_equivalente x quantidade) é o denominador do coeficiente de
 * proporcionalidade de TODAS as unidades da construção (Quadro II, coluna 31).
 * Uma quantidade errada em uma tipologia distorce o custo de todas as demais,
 * não só o da tipologia errada.
 */
class QuantidadeCorrespondeAosNumeros implements ValidationRule
{
    public function __construct(private readonly ?string $numero)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Lista vazia é tratada pela regra `required` do próprio campo
        if (blank($this->numero)) {
            return;
        }

        $listados = count(UnidadeAutonoma::separarNumeros($this->numero));

        if ($listados !== (int) $value) {
            $fail(
                "A lista de números tem {$listados} unidade(s), mas a quantidade informada é {$value}. "
                . 'Os dois valores precisam ser iguais — a quantidade multiplica as áreas no cálculo do orçamento.'
            );
        }
    }
}
