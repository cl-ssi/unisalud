<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AddressVia: string implements HasLabel
{
    case Calle = 'calle';
    case Pasaje = 'pasaje';
    case Avenida = 'avenida';
    case Otro = 'otro';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Calle => 'Calle',
            self::Pasaje => 'Pasaje',
            self::Avenida => 'Avenida',
            self::Otro => 'Otro',
        };
    }
}
