<?php

namespace App\Models;

use App\Models\Concerns\ExcluiDefinitivamente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Acabamento extends Model
{
    use ExcluiDefinitivamente;
    use SoftDeletes;

    /**
     * Sugestões para o campo Dependência. Não é uma lista fechada: o campo
     * aceita qualquer texto, e as dependências já usadas também são sugeridas.
     */
    public const DEPENDENCIAS = [
        // Privativas
        'Sala de estar', 'Sala de jantar', 'Sala de estar/jantar', 'Quarto', 'Suíte',
        'Closet', 'Banheiro', 'Lavabo', 'Cozinha', 'Área de serviço', 'Varanda',
        'Sacada', 'Terraço', 'Escritório', 'Despensa', 'Depósito',
        // De uso comum
        'Hall de entrada', 'Hall dos elevadores', 'Circulação', 'Escadaria',
        'Portaria', 'Guarita', 'Salão de festas', 'Espaço gourmet', 'Academia',
        'Piscina', 'Playground', 'Brinquedoteca', 'Garagem', 'Bicicletário',
        'Depósito de lixo', 'Casa de máquinas',
    ];

    /** Campos de texto livre padronizados ao gravar (ver booted()). */
    private const CAMPOS_DESCRITIVOS = [
        'piso_revestimento', 'piso_acabamento', 'piso_soleira',
        'parede_revestimento', 'parede_acabamento', 'parede_rodape',
        'teto_revestimento', 'teto_acabamento', 'peitoris',
    ];

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

    /**
     * A lista padrão mais as dependências já cadastradas. Células agrupadas
     * ("Sala de estar, Cozinha") viram sugestões separadas.
     *
     * @return list<string>
     */
    public static function sugestoesDeDependencia(): array
    {
        $usadas = static::withTrashed()->distinct()->pluck('dependencia')
            ->flatMap(fn (string $valor): array => explode(',', $valor))
            ->map(fn (string $item): string => trim($item))
            ->filter();

        return collect(self::DEPENDENCIAS)->merge($usadas)->unique()->values()->all();
    }

    protected static function booted(): void
    {
        // Padroniza a grafia ao gravar, para que "porcelanato  polido" e
        // "Porcelanato polido" contem como o mesmo valor num relatório. Fica no
        // model, e não na tela, porque acabamentos são gravados por vários
        // caminhos: a tela de acabamentos, os formulários dentro da construção
        // e da unidade, e a ação Duplicar.
        static::saving(function (Acabamento $acabamento): void {
            foreach (self::CAMPOS_DESCRITIVOS as $campo) {
                $acabamento->{$campo} = self::normalizarTexto($acabamento->{$campo});
            }

            $acabamento->dependencia = self::normalizarDependencias($acabamento->dependencia);
        });
    }

    /**
     * Junta espaços repetidos (inclusive os "invisíveis" de texto colado do
     * Word ou do Excel), tira os das pontas e põe a primeira letra em
     * maiúscula. Só a primeira: marcas e medidas no meio do texto ficam como
     * foram digitadas. Quebras de linha são preservadas; texto vazio vira null.
     */
    private static function normalizarTexto(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $limpo = preg_replace(['/[ \t\x{00A0}\x{2007}\x{202F}]+/u', '/ ?\R ?/u'], [' ', "\n"], $valor);

        // preg_replace devolve null diante de UTF-8 inválido: o texto fica como veio.
        if ($limpo === null) {
            return $valor;
        }

        $limpo = trim($limpo);

        return $limpo === '' ? null : mb_strtoupper(mb_substr($limpo, 0, 1)) . mb_substr($limpo, 1);
    }

    /** Cada dependência normalizada, separadas por ", " (o separador do campo de etiquetas). */
    private static function normalizarDependencias(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $itens = array_filter(
            array_map(self::normalizarTexto(...), explode(',', str_replace(["\r\n", "\r", "\n"], ',', $valor))),
            fn (?string $item): bool => $item !== null,
        );

        return $itens === [] ? null : implode(', ', $itens);
    }
}
