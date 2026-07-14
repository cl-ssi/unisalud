<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SurgicalComplexity: string implements HasLabel
{
    case CirugiaMayor = 'cirugia_mayor';
    case CirugiaMenor = 'cirugia_menor';
    case Procedimiento = 'procedimiento';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CirugiaMayor => 'Cirugía Mayor',
            self::CirugiaMenor => 'Cirugía Menor',
            self::Procedimiento => 'Procedimiento',
        };
    }
}
