<?php

namespace App\Rules;

use App\Models\UnidadeAutonoma;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Impede que o mesmo número apareça duas vezes na lista de uma unidade.
 *
 * A causa mais comum não é erro de digitação: os Quadros agrupam a tipologia
 * inteira num bloco só, mesmo quando ela ocupa mais de uma torre. No Malibu o
 * Apartamento Tipo F reúne 112 unidades porque os mesmos 56 números
 * ("102, 103, 106, 107...") existem na Torre Leste e na Torre Oeste.
 *
 * Como a unidade pertence a um pavimento, e o pavimento a uma torre, esse
 * bloco precisa virar um registro por torre — daí a repetição ser recusada.
 */
class NumerosSemDuplicidade implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $duplicados = UnidadeAutonoma::numerosDuplicados(is_string($value) ? $value : null);

        if ($duplicados === []) {
            return;
        }

        $amostra = array_slice($duplicados, 0, 5);
        $resto = count($duplicados) - count($amostra);

        $fail(
            'Número(s) repetido(s) na lista: ' . implode(', ', $amostra)
            . ($resto > 0 ? " e mais {$resto}" : '') . '. '
            . 'Se a tipologia ocupa mais de uma torre, cadastre um registro por torre — '
            . 'o pavimento de cada registro é que identifica a torre.'
        );
    }
}
