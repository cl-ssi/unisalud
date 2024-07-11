<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum ParticipantStatus: string implements HasLabel
{   
    case Acepted    = 'accepted';
    case Declined   = 'declined';
    case Tentative  = 'tentative';
    case NeedAction = 'need-action';

    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Acepted       => 'Aceptada',
            self::Declined      => 'Declinada',
            self::Tentative     => 'Tentativa',
            self::NeedAction    => 'Necesita Acción'
        };
    }
}