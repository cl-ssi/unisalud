<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum ParticipantRequired: string implements HasLabel
{   
    case Required           = 'required';
    case Optional           = 'optional';
    case InformationOnly    = 'information-only';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Required          => 'Requerido',
            self::Optional          => 'Opcional',
            self::InformationOnly   => 'Solo informacion'
        };
    }
}