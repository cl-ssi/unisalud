<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum ConditionClinicalStatus: string implements HasLabel
{   
    case Active     = 'active';
    case Recurrence = 'recurrence';
    case Relapse    = 'relapse';
    case Inactive   = 'inactive';
    case Remission  = 'remission';
    case Resolved   = 'resolved';
    case Unknown    = 'unknown';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active        => 'Activa',
            self::Recurrence    => 'Recurrente',
            self::Relapse       => 'Recaída',
            self::Inactive      => 'Inactiva',
            self::Remission     => 'Remisión',
            self::Resolved      => 'Resuelto',
            self::Unknown       => 'Desconocido',
        };
    }
}