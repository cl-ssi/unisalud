<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ConditionDependency: string implements HasColor, HasLabel
{
    case ELECTRO    = 'electrodependencia';
    case MOVIRED    = 'movilidad reducida';
    case OXIDEP     = 'oxigeno dependiente';
    case ENTERAL    = 'alimentacion enteral';
    case ONCO       = 'oncologicos';
    case CPU        = 'cuidados paliativos universales';
    case NANEAS     = 'naneas';
    case AVNI       = 'asistencia ventilatoria no invasiva';
    case AVI        = 'asistencia ventilatoria invasiva';
    case CONOXI     = 'concentradores de oxigeno';
    case DEMENCIA   = 'demencia';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ELECTRO    => 'Electrodependencia',
            self::MOVIRED    => 'Movilidad Reducida',
            self::OXIDEP     => 'Oxigeno Dependiente',
            self::ENTERAL    => 'Alimentacion Enteral',
            self::ONCO       => 'Oncologicos',
            self::CPU        => 'Cuidados Paliativos Universales',
            self::NANEAS     => 'Naneas',
            self::AVNI       => 'Asistencia Ventilatoria no Invasiva',
            self::AVI        => 'Asistencia Ventilatoria Invasiva',
            self::CONOXI     => 'Concentradores de Oxigeno',
            self::DEMENCIA   => 'Demencia',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::ELECTRO   => 'fuchsia',
            self::AVNI      => 'fuchsia',
            self::AVI       => 'fuchsia',
            self::CONOXI    => 'amber',
            self::MOVIRED   => 'sky',
            self::OXIDEP    => 'violet',
            self::ENTERAL   => 'lime',
            self::ONCO      => 'teal',
            self::CPU       => 'orange',
            self::NANEAS    => 'stone',
            self::DEMENCIA  => 'slate',
            default         => 'primary',
        };
    }
}
