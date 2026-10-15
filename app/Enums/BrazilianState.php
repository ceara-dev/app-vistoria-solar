<?php

namespace App\Enums;

/**
 * Unidades federativas, pela sigla.
 */
enum BrazilianState: string
{
    case Ac = 'AC';
    case Al = 'AL';
    case Ap = 'AP';
    case Am = 'AM';
    case Ba = 'BA';
    case Ce = 'CE';
    case Df = 'DF';
    case Es = 'ES';
    case Go = 'GO';
    case Ma = 'MA';
    case Mt = 'MT';
    case Ms = 'MS';
    case Mg = 'MG';
    case Pa = 'PA';
    case Pb = 'PB';
    case Pr = 'PR';
    case Pe = 'PE';
    case Pi = 'PI';
    case Rj = 'RJ';
    case Rn = 'RN';
    case Rs = 'RS';
    case Ro = 'RO';
    case Rr = 'RR';
    case Sc = 'SC';
    case Sp = 'SP';
    case Se = 'SE';
    case To = 'TO';

    public function label(): string
    {
        return match ($this) {
            self::Ac => 'Acre',
            self::Al => 'Alagoas',
            self::Ap => 'Amapá',
            self::Am => 'Amazonas',
            self::Ba => 'Bahia',
            self::Ce => 'Ceará',
            self::Df => 'Distrito Federal',
            self::Es => 'Espírito Santo',
            self::Go => 'Goiás',
            self::Ma => 'Maranhão',
            self::Mt => 'Mato Grosso',
            self::Ms => 'Mato Grosso do Sul',
            self::Mg => 'Minas Gerais',
            self::Pa => 'Pará',
            self::Pb => 'Paraíba',
            self::Pr => 'Paraná',
            self::Pe => 'Pernambuco',
            self::Pi => 'Piauí',
            self::Rj => 'Rio de Janeiro',
            self::Rn => 'Rio Grande do Norte',
            self::Rs => 'Rio Grande do Sul',
            self::Ro => 'Rondônia',
            self::Rr => 'Roraima',
            self::Sc => 'Santa Catarina',
            self::Sp => 'São Paulo',
            self::Se => 'Sergipe',
            self::To => 'Tocantins',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $state): array => ['value' => $state->value, 'label' => "{$state->value} - {$state->label()}"],
            self::cases(),
        );
    }
}
