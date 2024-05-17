<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasLabel
{
    case Female = 'female';
    case Male = 'male';
    case NonBinary = 'non-binary';
    case TransgenderMale = 'transgender-male';
    case TransgenderFemale = 'transgender-female';
    case Other = 'other';
    case NonDisclose = 'non-disclose';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Female => 'Mujer',
            self::Male => 'Hombre',
            self::NonBinary => 'No binario',
            self::TransgenderMale => 'Transgénero masculino',
            self::TransgenderFemale => 'Transgénero femenino',
            self::Other => 'Otro',
            self::NonDisclose => 'No revelar',
        };
    }
}