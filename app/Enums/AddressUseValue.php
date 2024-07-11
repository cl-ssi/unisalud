<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum AddressUseValue: string implements HasLabel
{   
    // use column
    case Home       = 'home';
    case Work       = 'work';
    case Temp       = 'temp';
    case Old        = 'old';
    case Billing    = 'billing';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Home      => 'Casa',
            self::Work      => 'Lugar de Trabajo',
            self::Temp      => 'Temporal',
            self::Old       => 'Antigua Residencia',
            self::Billing   => 'Lugar de Facturación'
        };
    }
}