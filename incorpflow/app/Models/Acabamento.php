<?php

namespace App\Models;

use App\Models\Concerns\ExcluiDefinitivamente;
use Collator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Acabamento extends Model
{
    use ExcluiDefinitivamente;
    use SoftDeletes;

    /** Coluna do SoftDeletes (inativar). deleted_at é a exclusão definitiva. */
    const DELETED_AT = 'inactive_at';

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

        return collect(self::DEPENDENCIAS)->merge($usadas)->unique()
            ->sort((new Collator('pt_BR'))->compare(...))->values()->all();
    }

    /**
     * Dependências que já têm acabamento no mesmo vínculo e escopo. Cada
     * dependência tem um só acabamento por quadro: "Cozinha" não pode estar em
     * duas linhas, nem sozinha numa e agrupada ("Sala de estar, Cozinha") noutra.
     * Sem vínculo, a comparação é com os outros acabamentos sem vínculo.
     * Inativos contam, porque podem ser reativados; excluídos não.
     *
     * @param  list<string>|string|null  $dependencias
     * @return list<string> as repetidas, com a grafia já gravada
     */
    public static function dependenciasRepetidas(
        array|string|null $dependencias,
        ?string $acabavelType,
        mixed $acabavelId,
        ?string $escopo,
        mixed $ignorarId = null,
    ): array {
        $novas = array_map(mb_strtolower(...), self::itensDeDependencia($dependencias));

        if ($novas === [] || blank($escopo)) {
            return [];
        }

        return static::withTrashed()
            ->where('escopo', $escopo)
            ->when(
                filled($acabavelId),
                fn ($query) => $query->where('acabavel_type', $acabavelType)->where('acabavel_id', $acabavelId),
                fn ($query) => $query->whereNull('acabavel_id'),
            )
            ->when(filled($ignorarId), fn ($query) => $query->whereKeyNot($ignorarId))
            ->pluck('dependencia')
            ->flatMap(self::itensDeDependencia(...))
            ->filter(fn (string $item): bool => in_array(mb_strtolower($item), $novas, true))
            ->unique()
            ->values()
            ->all();
    }

    /** @param  list<string>  $repetidas */
    public static function mensagemDeRepetidas(array $repetidas): string
    {
        $nomes = implode(', ', $repetidas);

        return (count($repetidas) === 1 ? "{$nomes} já tem acabamento" : "{$nomes} já têm acabamento")
            . ' cadastrado neste vínculo e escopo (confira também os inativos).';
    }

    protected static function booted(): void
    {
        // Padroniza a grafia ao gravar, para que "porcelanato  polido" e
        // "Porcelanato polido" contem como o mesmo valor num relatório. Fica no
        // model, e não na tela, porque acabamentos são gravados por vários
        // caminhos: a tela de acabamentos e os formulários dentro da construção
        // e da unidade.
        static::saving(function (Acabamento $acabamento): void {
            foreach (self::CAMPOS_DESCRITIVOS as $campo) {
                $acabamento->{$campo} = self::normalizarTexto($acabamento->{$campo});
            }

            $acabamento->dependencia = self::normalizarDependencias($acabamento->dependencia);

            // O formulário já valida isto (AcabamentoResource::campoDependencia);
            // aqui vale para qualquer outro caminho de gravação.
            $repetidas = self::dependenciasRepetidas(
                $acabamento->dependencia,
                $acabamento->acabavel_type,
                $acabamento->acabavel_id,
                $acabamento->escopo,
                $acabamento->getKey(),
            );

            if ($repetidas !== []) {
                throw ValidationException::withMessages(['dependencia' => self::mensagemDeRepetidas($repetidas)]);
            }
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
        $itens = self::itensDeDependencia($valor);

        return $itens === [] ? null : implode(', ', $itens);
    }

    /**
     * Separa as dependências (vírgula ou quebra de linha), normaliza cada uma
     * e tira as repetidas dentro do mesmo registro, sem diferenciar maiúsculas.
     *
     * @param  list<string>|string|null  $valor  texto gravado ou etiquetas do formulário
     * @return list<string>
     */
    private static function itensDeDependencia(array|string|null $valor): array
    {
        if ($valor === null) {
            return [];
        }

        $texto = is_array($valor) ? implode(',', $valor) : $valor;
        $itens = [];

        foreach (explode(',', str_replace(["\r\n", "\r", "\n"], ',', $texto)) as $item) {
            $item = self::normalizarTexto($item);

            if ($item !== null) {
                $itens[mb_strtolower($item)] ??= $item;
            }
        }

        return array_values($itens);
    }
}
