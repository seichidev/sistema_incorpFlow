<?php

/**
 * Teste funcional do IncorpFlow.
 *
 * Percorre o CRUD das cinco entidades, a cascata de inativação e reativação,
 * as regras de validação, as restrições do banco e as consultas da listagem.
 *
 * Tudo roda dentro de uma transação desfeita ao final: nenhum dado é alterado.
 *
 *   php teste_funcional.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Acabamento;
use App\Models\Construcao;
use App\Models\Equipamento;
use App\Models\Pavimento;
use App\Models\UnidadeAutonoma;
use App\Models\User;
use App\Rules\NumerosSemDuplicidade;
use App\Rules\QuantidadeCorrespondeAosNumeros;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$passou = 0;
$falhou = 0;

function secao(string $titulo): void
{
    echo PHP_EOL . '── ' . $titulo . ' ' . str_repeat('─', max(0, 58 - mb_strlen($titulo))) . PHP_EOL;
}

function verifica(string $descricao, bool $condicao, string $detalhe = ''): void
{
    global $passou, $falhou;
    $condicao ? $passou++ : $falhou++;
    printf("  %s  %-52s %s" . PHP_EOL, $condicao ? 'ok  ' : 'FALHA', $descricao, $detalhe);
}

/** Executa a regra de validação e devolve a mensagem de erro, ou null se passou. */
function aplicaRegra($regra, mixed $valor, string $campo = 'campo'): ?string
{
    $erro = null;
    $regra->validate($campo, $valor, function (string $mensagem) use (&$erro) {
        $erro = $mensagem;
    });

    return $erro;
}

try {
    DB::transaction(function () {

        // ══════════════ 1. CONSTRUÇÃO ══════════════
        secao('1. Construção — CRUD');

        $antes = Construcao::count();
        $c = Construcao::create([
            'nome' => 'Teste Funcional', 'endereco' => 'Rua de Teste, 1',
            'cidade' => 'Londrina', 'estado' => 'PR', 'tipo' => 'Residencial',
            'status' => 'Planejamento', 'responsavel' => 'Eng. Teste',
            'descricao' => 'Registro criado pelo teste automatizado.',
        ]);
        verifica('criar', $c->exists && Construcao::count() === $antes + 1, "id {$c->id}");

        verifica('ler', Construcao::find($c->id)?->nome === 'Teste Funcional');

        $c->update(['status' => 'Em construção', 'cidade' => 'Maringá']);
        verifica('editar', Construcao::find($c->id)->cidade === 'Maringá');

        verifica('listar', Construcao::count() === $antes + 1, ($antes + 1) . ' registros');

        // ══════════════ 2. PAVIMENTO ══════════════
        secao('2. Pavimento — CRUD e vínculo');

        $p = $c->pavimentos()->create([
            'nome' => 'Térreo de Teste', 'tipo' => 'Terreo', 'torre' => 'T',
            'nivel' => 0, 'quantidade' => 3, 'area_construida' => 250.5000,
        ]);
        verifica('criar vinculado à construção', $p->exists && $p->construcao_id === $c->id);
        verifica('relação inversa (belongsTo)', $p->construcao->nome === 'Teste Funcional');
        verifica('relação direta (hasMany)', $c->pavimentos()->count() === 1);

        $p->update(['area_construida' => 300.1234]);
        verifica('editar, decimal com 4 casas', (string) Pavimento::find($p->id)->area_construida === '300.1234');

        // ══════════════ 3. UNIDADE AUTÔNOMA ══════════════
        secao('3. Unidade Autônoma — CRUD e tipologia');

        $u = $p->unidadesAutonomas()->create([
            'numero' => '101, 201, 301', 'tipo' => 'Apartamento', 'tipologia' => 'Tipo Teste',
            'permutada' => false, 'quantidade' => 3,
            'area_privativa' => 65.4321, 'area_comum' => 18.1234, 'area_equivalente' => 83.5555,
        ]);
        verifica('criar vinculada ao pavimento', $u->exists && $u->pavimento_id === $p->id);
        verifica('separarNumeros()', UnidadeAutonoma::separarNumeros($u->numero) === ['101', '201', '301']);
        verifica('atributo /quantidade_listada', $u->quantidade_listada === 3);
        verifica('atributo /quantidade_confere', $u->quantidade_confere === true);
        verifica('numero como text (lista longa)', strlen($u->numero) > 0 && str_contains($u->numero, ','));

        // ══════════════ 4. EQUIPAMENTO E ACABAMENTO ══════════════
        secao('4. Equipamento e Acabamento');

        $e = $c->equipamentos()->create([
            'nome' => 'Elevador de Teste', 'tipo_ou_marca' => 'Marca X',
            'acabamento' => 'Inox', 'detalhes_gerais' => 'Detalhe de teste.',
        ]);
        verifica('criar equipamento', $e->exists && $c->equipamentos()->count() === 1);

        $aC = $c->acabamentos()->create(['escopo' => 'Comum', 'dependencia' => 'Hall de Teste']);
        $aU = $u->acabamentos()->create(['escopo' => 'Privativo', 'dependencia' => 'Sala de Teste']);
        verifica('acabamento de construção (morphMany)', $aC->acabavel_type === Construcao::class);
        verifica('acabamento de unidade (morphMany)', $aU->acabavel_type === UnidadeAutonoma::class);
        verifica('relação inversa (morphTo)', $aC->acabavel->is($c) && $aU->acabavel->is($u));

        // ══════════════ 5. CASCATA ══════════════
        secao('5. Inativação em cascata');

        $c->delete();
        verifica('construção sai da listagem', Construcao::find($c->id) === null);
        verifica('pavimento acompanha', Pavimento::find($p->id) === null);
        verifica('unidade acompanha', UnidadeAutonoma::find($u->id) === null);
        verifica('equipamento acompanha', Equipamento::find($e->id) === null);
        verifica('acabamentos acompanham', Acabamento::whereIn('id', [$aC->id, $aU->id])->count() === 0);
        verifica('registros preservados no banco', Construcao::withTrashed()->find($c->id) !== null);
        verifica('deleted_at preenchido', Construcao::withTrashed()->find($c->id)->deleted_at !== null);

        // ══════════════ 6. REATIVAÇÃO ══════════════
        secao('6. Reativação em cascata');

        Construcao::withTrashed()->find($c->id)->restore();
        verifica('construção volta', Construcao::find($c->id) !== null);
        verifica('pavimento volta', Pavimento::find($p->id) !== null);
        verifica('unidade volta', UnidadeAutonoma::find($u->id) !== null);
        verifica('equipamento volta', Equipamento::find($e->id) !== null);
        verifica('acabamentos voltam', Acabamento::whereIn('id', [$aC->id, $aU->id])->count() === 2);

        // ══════════════ 7. VALIDAÇÕES ══════════════
        secao('7. Regras de validação');

        $r = new QuantidadeCorrespondeAosNumeros('101, 102, 103');
        verifica('quantidade bate com a lista', aplicaRegra($r, 3) === null);

        $erro = aplicaRegra($r, 2);
        verifica('quantidade divergente é recusada', $erro !== null,
            $erro ? '"' . mb_substr($erro, 0, 44) . '..."' : '');

        $rd = new NumerosSemDuplicidade();
        verifica('lista sem repetição passa', aplicaRegra($rd, '101, 102, 103') === null);

        $erroDup = aplicaRegra($rd, '101, 102, 101, 103, 102');
        verifica('lista com repetição é recusada', $erroDup !== null,
            $erroDup ? '"' . mb_substr($erroDup, 0, 44) . '..."' : '');

        verifica('numerosDuplicados() identifica quais',
            UnidadeAutonoma::numerosDuplicados('101, 102, 101, 103, 102') === ['101', '102']);

        verifica('separarNumeros() aceita "e" e quebra de linha',
            UnidadeAutonoma::separarNumeros("101 e 102\n103") === ['101', '102', '103']);

        // ══════════════ 8. RESTRIÇÕES DO BANCO ══════════════
        secao('8. Restrições do banco');

        // Qualquer QueryException significa que o banco recusou a escrita.
        // Comparar a mensagem seria frágil: o texto do PostgreSQL varia com o idioma.
        $recusou = function (callable $fn): bool {
            try {
                DB::transaction(fn () => $fn());
                return false;
            } catch (Illuminate\Database\QueryException $e) {
                return true;
            }
        };

        verifica('tipo de construção fora da lista é recusado',
            $recusou(fn () => DB::table('construcoes')->insert([
                'nome' => 'x', 'endereco' => 'x', 'cidade' => 'x', 'estado' => 'PR',
                'tipo' => 'Inexistente', 'status' => 'Planejamento', 'responsavel' => 'x',
            ])));

        verifica('situação de obra fora da lista é recusada',
            $recusou(fn () => DB::table('construcoes')->insert([
                'nome' => 'x', 'endereco' => 'x', 'cidade' => 'x', 'estado' => 'PR',
                'tipo' => 'Residencial', 'status' => 'Inexistente', 'responsavel' => 'x',
            ])));

        verifica('tipo de pavimento fora da lista é recusado',
            $recusou(fn () => DB::table('pavimentos')->insert([
                'construcao_id' => $c->id, 'nome' => 'x', 'tipo' => 'Inexistente', 'quantidade' => 1,
            ])));

        verifica('tipo de unidade fora da lista é recusado',
            $recusou(fn () => DB::table('unidades_autonomas')->insert([
                'pavimento_id' => $p->id, 'numero' => '1', 'tipo' => 'Inexistente',
                'permutada' => false, 'quantidade' => 1,
            ])));

        verifica('escopo de acabamento fora da lista é recusado',
            $recusou(fn () => DB::table('acabamentos')->insert([
                'acabavel_type' => Construcao::class, 'acabavel_id' => $c->id,
                'escopo' => 'Inexistente', 'dependencia' => 'x',
            ])));

        verifica('pavimento sem construção é recusado',
            $recusou(fn () => DB::table('pavimentos')->insert([
                'construcao_id' => 999999, 'nome' => 'x', 'tipo' => 'Terreo', 'quantidade' => 1,
            ])));

        verifica('e-mail duplicado é recusado',
            $recusou(fn () => DB::table('users')->insert([
                'name' => 'x', 'email' => User::first()->email, 'password' => 'x',
            ])));

        // ══════════════ 9. CONSULTAS DA LISTAGEM ══════════════
        secao('9. Consultas da listagem');

        $linha = App\Filament\Resources\ConstrucaoResource::getEloquentQuery()
            ->where('nome', 'Teste Funcional')->first();
        verifica('total de pavimentos soma as repetições', (int) $linha->total_pavimentos === 3, '1 linha × quantidade 3');
        verifica('total de unidades soma as quantidades', (int) $linha->total_unidades === 3);

        DB::enableQueryLog();
        App\Filament\Resources\ConstrucaoResource::getEloquentQuery()->get();
        $n = count(DB::getQueryLog());
        DB::disableQueryLog();
        verifica('listagem em consulta única (sem N+1)', $n === 1, "{$n} consulta");

        $c->delete();
        verifica('padrão do filtro esconde inativos',
            App\Filament\Resources\ConstrucaoResource::getEloquentQuery()->withoutTrashed()
                ->where('nome', 'Teste Funcional')->count() === 0);
        verifica('filtro "somente inativos" encontra',
            App\Filament\Resources\ConstrucaoResource::getEloquentQuery()->onlyTrashed()
                ->where('nome', 'Teste Funcional')->count() === 1);
        $c->restore();

        // ══════════════ 10. AUTENTICAÇÃO ══════════════
        secao('10. Autenticação');

        $u1 = User::where('email', 'teste@gmail.com')->first();
        verifica('usuário de teste existe', $u1 !== null);
        verifica('senha confere', $u1 && Hash::check('teste', $u1->password));
        verifica('senha não está em texto legível', $u1 && $u1->password !== 'teste');
        verifica('senha rejeita valor errado', $u1 && ! Hash::check('errada', $u1->password));

        // ══════════════ 11. EXCLUSÃO DEFINITIVA ══════════════
        secao('11. Exclusão definitiva');

        $idPavimento = $p->id;
        $idUnidade = $u->id;
        Construcao::withTrashed()->find($c->id)->forceDelete();
        verifica('construção sai do banco', Construcao::withTrashed()->find($c->id) === null);
        verifica('pavimento sai por CASCADE do banco', Pavimento::withTrashed()->find($idPavimento) === null);
        verifica('unidade sai por CASCADE do banco', UnidadeAutonoma::withTrashed()->find($idUnidade) === null);

        throw new RuntimeException('__desfazer__');
    });
} catch (RuntimeException $e) {
    if ($e->getMessage() !== '__desfazer__') {
        throw $e;
    }
}

echo PHP_EOL . str_repeat('═', 62) . PHP_EOL;
printf('  %d verificações · %d ok · %d falha(s)' . PHP_EOL, $passou + $falhou, $passou, $falhou);
echo '  transação desfeita — nenhum dado foi alterado' . PHP_EOL;
echo str_repeat('═', 62) . PHP_EOL;

exit($falhou === 0 ? 0 : 1);
