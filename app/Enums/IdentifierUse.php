<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum IdentifierUse: string implements HasLabel
{   
    // use column
    case Usual      = 'usual';
    case Official   = 'official';
    case Temp       = 'temp';
    case Secondary  = 'secondary';
    case Old        = 'old';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Usual     => 'Habitual',
            self::Official  => 'Oficial',
            self::Temp      => 'Temporal',
            self::Secondary => 'Secundaria',
            self::Old       => 'Antigua'
        };
    }
}