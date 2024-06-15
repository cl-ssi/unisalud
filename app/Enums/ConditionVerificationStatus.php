<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum ConditionVerificationStatus: string implements HasLabel
{   
    case Unconfirmed    = 'unconfirmed';
    case Provisional    = 'provisional';
    case Differential   = 'differential';
    case Confirmed      = 'confirmed';
    case Refuted        = 'refuted';
    case EnteredInError = 'entered-in-error';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Unconfirmed       => 'Sin cofirmar',
            self::Provisional       => 'Provisional',
            self::Differential      => 'Diferencial',
            self::Confirmed         => 'Confirmado',
            self::Refuted           => 'Refutada',
            self::EnteredInError    => 'Ingresado por error',
        };
    }
}