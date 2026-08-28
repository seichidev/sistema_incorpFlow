<?php

namespace Database\Seeders;

use App\Models\Acabamento;
use App\Models\Construcao;
use App\Models\Equipamento;
use App\Models\Pavimento;
use App\Models\UnidadeAutonoma;
use Illuminate\Database\Seeder;

/**
 * Dados de exemplo para demonstração do sistema.
 *
 * Reproduz os empreendimentos das telas apresentadas no relatório. É idempotente:
 * rodar duas vezes não duplica registro, porque tudo passa por firstOrCreate.
 */
class ExemploSeeder extends Seeder
{
    public function run(): void
    {
        $aurora = $this->construcao([
            'nome'        => 'Residencial Aurora',
            'endereco'    => 'Av. Paulista, 1230',
            'cidade'      => 'São Paulo',
            'estado'      => 'SP',
            'tipo'        => 'Residencial',
            'status'      => 'Em construção',
            'responsavel' => 'Eng. Carlos Souza',
            'descricao'   => 'Edifício residencial de 12 pavimentos-tipo, com garagem no térreo.',
        ]);

        $centro = $this->construcao([
            'nome'        => 'Comercial Centro',
            'endereco'    => 'R. XV de Novembro, 450',
            'cidade'      => 'Curitiba',
            'estado'      => 'PR',
            'tipo'        => 'Comercial',
            'status'      => 'Concluída',
            'responsavel' => 'Eng. Ana Lima',
            'descricao'   => 'Edifício comercial com lojas no térreo e salas nos pavimentos superiores.',
        ]);

        $this->construcao([
            'nome'        => 'Edifício Paulista',
            'endereco'    => 'Av. Paulista, 2340',
            'cidade'      => 'São Paulo',
            'estado'      => 'SP',
            'tipo'        => 'Misto',
            'status'      => 'Planejamento',
            'responsavel' => 'Eng. Pedro Martins',
            'descricao'   => 'Empreendimento de uso misto, em fase de projeto.',
        ]);

        $bosque = $this->construcao([
            'nome'        => 'Residencial Bosque',
            'endereco'    => 'R. das Flores, 88',
            'cidade'      => 'Florianópolis',
            'estado'      => 'SC',
            'tipo'        => 'Residencial',
            'status'      => 'Em construção',
            'responsavel' => 'Eng. Mariana Costa',
            'descricao'   => 'Condomínio residencial com pilotis e área de lazer.',
        ]);

        $this->construcao([
            'nome'        => 'Torre Business',
            'endereco'    => 'Av. Brasil, 500',
            'cidade'      => 'Rio de Janeiro',
            'estado'      => 'RJ',
            'tipo'        => 'Comercial',
            'status'      => 'Suspensa',
            'responsavel' => 'Eng. Roberto Alves',
            'descricao'   => 'Obra suspensa por pendência de alvará.',
        ]);

        // ---------- Pavimentos ----------

        $auroraTerreo = $this->pavimento($aurora, [
            'nome'            => 'Pavimento Térreo',
            'tipo'            => 'Terreo',
            'torre'           => 'A',
            'nivel'           => 0,
            'quantidade'      => 1,
            'area_construida' => 420.0000,
        ]);

        $auroraTipo = $this->pavimento($aurora, [
            'nome'            => '1º Pavimento Tipo',
            'tipo'            => 'Tipo',
            'torre'           => 'A',
            'nivel'           => 1,
            'quantidade'      => 5,
            'area_construida' => 380.0000,
        ]);

        $this->pavimento($aurora, [
            'nome'            => 'Cobertura',
            'tipo'            => 'Superior',
            'torre'           => 'A',
            'nivel'           => 12,
            'quantidade'      => 1,
            'area_construida' => 280.0000,
        ]);

        $this->pavimento($centro, [
            'nome'            => 'Subsolo 1',
            'tipo'            => 'Inferior',
            'torre'           => 'Única',
            'nivel'           => -1,
            'quantidade'      => 1,
            'area_construida' => 560.0000,
        ]);

        $centroTerreo = $this->pavimento($centro, [
            'nome'            => 'Pavimento Térreo',
            'tipo'            => 'Terreo',
            'torre'           => 'Única',
            'nivel'           => 0,
            'quantidade'      => 1,
            'area_construida' => 480.0000,
        ]);

        $centroTipo = $this->pavimento($centro, [
            'nome'            => '1º Pavimento Tipo',
            'tipo'            => 'Tipo',
            'torre'           => 'Única',
            'nivel'           => 1,
            'quantidade'      => 6,
            'area_construida' => 460.0000,
        ]);

        $this->pavimento($bosque, [
            'nome'            => 'Pilotis',
            'tipo'            => 'Terreo',
            'torre'           => 'B',
            'nivel'           => 0,
            'quantidade'      => 1,
            'area_construida' => 320.0000,
        ]);

        // ---------- Unidades autônomas ----------

        $unidade101 = $this->unidade($auroraTipo, [
            'numero'           => '101',
            'tipo'             => 'Apartamento',
            'tipologia'        => 'Tipo A',
            'permutada'        => false,
            'quantidade'       => 1,
            'area_privativa'   => 65.4000,
            'area_comum'       => 18.2000,
            'area_equivalente' => 83.6000,
        ]);

        $this->unidade($auroraTipo, [
            'numero'           => '102',
            'tipo'             => 'Apartamento',
            'tipologia'        => 'Tipo B',
            'permutada'        => false,
            'quantidade'       => 1,
            'area_privativa'   => 72.8000,
            'area_comum'       => 20.1000,
            'area_equivalente' => 92.9000,
        ]);

        $this->unidade($auroraTipo, [
            'numero'           => '103',
            'tipo'             => 'Apartamento',
            'tipologia'        => 'Cobertura',
            'permutada'        => true,
            'quantidade'       => 1,
            'area_privativa'   => 145.6000,
            'area_comum'       => 35.2000,
            'area_equivalente' => 180.8000,
        ]);

        // Demonstra a regra própria do sistema: a lista de números tem que
        // ter exatamente 'quantidade' itens. São 4 números e quantidade 4.
        $this->unidade($auroraTipo, [
            'numero'           => '201, 301, 401, 501',
            'tipo'             => 'Apartamento',
            'tipologia'        => 'Tipo A',
            'permutada'        => false,
            'quantidade'       => 4,
            'area_privativa'   => 65.4000,
            'area_comum'       => 18.2000,
            'area_equivalente' => 83.6000,
        ]);

        $this->unidade($auroraTerreo, [
            'numero'           => 'G-01',
            'tipo'             => 'Vaga de Garagem',
            'tipologia'        => 'Vaga simples',
            'permutada'        => false,
            'quantidade'       => 1,
            'area_privativa'   => 12.5000,
            'area_comum'       => null,
            'area_equivalente' => 12.5000,
        ]);

        $this->unidade($centroTerreo, [
            'numero'           => 'L-01',
            'tipo'             => 'Loja',
            'tipologia'        => 'Loja de esquina',
            'permutada'        => false,
            'quantidade'       => 1,
            'area_privativa'   => 48.3000,
            'area_comum'       => 12.4000,
            'area_equivalente' => 60.7000,
        ]);

        $this->unidade($centroTipo, [
            'numero'           => 'S-101',
            'tipo'             => 'Sala Comercial',
            'tipologia'        => 'Sala padrão',
            'permutada'        => false,
            'quantidade'       => 1,
            'area_privativa'   => 38.2000,
            'area_comum'       => 9.8000,
            'area_equivalente' => 48.0000,
        ]);

        $this->unidade($centroTipo, [
            'numero'           => 'S-102',
            'tipo'             => 'Sala Comercial',
            'tipologia'        => 'Sala padrão',
            'permutada'        => true,
            'quantidade'       => 1,
            'area_privativa'   => 38.2000,
            'area_comum'       => 9.8000,
            'area_equivalente' => 48.0000,
        ]);

        // ---------- Equipamentos ----------

        $this->equipamento($aurora, [
            'nome'            => 'Elevador social',
            'tipo_ou_marca'   => 'Atlas Schindler, modelo 3300',
            'acabamento'      => 'Cabine em aço inox escovado',
            'detalhes_gerais' => 'Dois elevadores por torre, capacidade de 8 passageiros.',
        ]);

        $this->equipamento($aurora, [
            'nome'            => 'Portão de garagem',
            'tipo_ou_marca'   => 'Garen, automatizador deslizante',
            'acabamento'      => 'Chapa metálica com pintura eletrostática',
            'detalhes_gerais' => 'Acionamento por controle remoto e interfone.',
        ]);

        $this->equipamento($centro, [
            'nome'            => 'Central de ar-condicionado',
            'tipo_ou_marca'   => 'Carrier, sistema VRF',
            'acabamento'      => 'Difusores em alumínio anodizado',
            'detalhes_gerais' => 'Atende às salas comerciais dos pavimentos-tipo.',
        ]);

        // ---------- Acabamentos ----------

        $this->acabamento($aurora, [
            'escopo'              => 'Comum',
            'dependencia'         => 'Hall de entrada',
            'piso_revestimento'   => 'Porcelanato polido 90x90 cm',
            'piso_acabamento'     => 'Rejunte epóxi cinza',
            'piso_soleira'        => 'Granito cinza andorinha',
            'parede_revestimento' => 'Massa corrida sobre alvenaria',
            'parede_acabamento'   => 'Tinta acrílica acetinada',
            'parede_rodape'       => 'Poliestireno 10 cm, pintado',
            'teto_revestimento'   => 'Forro de gesso acartonado',
            'teto_acabamento'     => 'Tinta acrílica fosca branca',
            'peitoris'            => 'Não se aplica',
        ]);

        $this->acabamento($unidade101, [
            'escopo'              => 'Privativo',
            'dependencia'         => 'Sala de estar',
            'piso_revestimento'   => 'Porcelanato acetinado 60x60 cm',
            'piso_acabamento'     => 'Rejunte flexível cinza claro',
            'piso_soleira'        => 'Granito preto São Gabriel',
            'parede_revestimento' => 'Massa corrida sobre alvenaria',
            'parede_acabamento'   => 'Tinta acrílica fosca',
            'parede_rodape'       => 'Poliestireno 7 cm, pintado',
            'teto_revestimento'   => 'Laje rebocada',
            'teto_acabamento'     => 'Tinta acrílica fosca branca',
            'peitoris'            => 'Granito cinza, 15 cm de largura',
        ]);

        $this->command?->info('Exemplos criados: '
            . Construcao::count() . ' construções, '
            . Pavimento::count() . ' pavimentos, '
            . UnidadeAutonoma::count() . ' unidades, '
            . Equipamento::count() . ' equipamentos, '
            . Acabamento::count() . ' acabamentos.');
    }

    private function construcao(array $dados): Construcao
    {
        return Construcao::firstOrCreate(['nome' => $dados['nome']], $dados);
    }

    private function pavimento(Construcao $construcao, array $dados): Pavimento
    {
        return $construcao->pavimentos()->firstOrCreate(
            ['nome' => $dados['nome'], 'torre' => $dados['torre']],
            $dados
        );
    }

    private function unidade(Pavimento $pavimento, array $dados): UnidadeAutonoma
    {
        return $pavimento->unidadesAutonomas()->firstOrCreate(
            ['numero' => $dados['numero']],
            $dados
        );
    }

    private function equipamento(Construcao $construcao, array $dados): Equipamento
    {
        return $construcao->equipamentos()->firstOrCreate(
            ['nome' => $dados['nome']],
            $dados
        );
    }

    private function acabamento(Construcao|UnidadeAutonoma $dono, array $dados): Acabamento
    {
        return $dono->acabamentos()->firstOrCreate(
            ['dependencia' => $dados['dependencia'], 'escopo' => $dados['escopo']],
            $dados
        );
    }
}
