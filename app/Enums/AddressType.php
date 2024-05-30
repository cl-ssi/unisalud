<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum AddressType: string implements HasLabel
{   
    case Postal     = 'postal';
    case Physical   = 'physical';
    case Both       = 'both';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Postal    => 'Postal',
            self::Physical  => 'Física',
            self::Both      => 'Ambas',
        };
    }
}