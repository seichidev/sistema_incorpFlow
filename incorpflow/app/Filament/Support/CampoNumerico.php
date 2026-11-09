<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;

/**
 * Campos numéricos limitados ao que a coluna do banco consegue guardar.
 *
 * No navegador, o atributo data-casas-decimais faz o script de
 * resources/views/filament/scripts.blade.php cortar as casas a mais
 * enquanto se digita. No servidor, as regras repetem o limite: sem elas, um
 * valor grande demais chegaria ao banco e viraria erro de sistema, e casas a
 * mais seriam arredondadas sem aviso.
 */
class CampoNumerico
{
    /**
     * Para colunas decimal(digitos, casas). Ex.: decimal(12, 4) aceita até
     * 99999999.9999.
     */
    public static function decimal(string $nome, int $digitos, int $casas): TextInput
    {
        $maximo = (float) (str_repeat('9', $digitos - $casas) . '.' . str_repeat('9', $casas));

        return TextInput::make($nome)
            ->numeric()
            ->step(1 / (10 ** $casas))
            ->minValue(-$maximo)
            ->maxValue($maximo)
            ->rule("decimal:0,{$casas}")
            ->validationMessages(['decimal' => "O campo :attribute aceita no máximo {$casas} casas decimais."])
            ->extraInputAttributes(['data-casas-decimais' => $casas]);
    }

    /** Para colunas integer do PostgreSQL. */
    public static function inteiro(string $nome): TextInput
    {
        return TextInput::make($nome)
            ->integer()
            ->maxValue(2147483647)
            ->extraInputAttributes(['data-casas-decimais' => 0]);
    }
}
