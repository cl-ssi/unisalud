<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum AppointmentType: string implements HasLabel
{   
    case CHECKUP        = 'CHECKUP';
    case EMERGENCY      = 'EMERGENCY';
    case FOLLOWUP       = 'FOLLOWUP';
    case ROUTINE        = 'ROUTINE';
    case WALKIN         = 'WALKIN';
    case OVERBOOKING    = 'OVERBOOKING';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::CHECKUP       => 'Chequeo',
            self::EMERGENCY     => 'Urgencia',
            self::FOLLOWUP      => 'Seguimiento',
            self::ROUTINE       => 'Rutinaria',
            self::WALKIN        => 'walk in',
            self::OVERBOOKING   => 'Sobrecupo',
        };
    }

    //  'CHECKUP, EMERGENCY, FOLLOWUP, ROUTINE, WALKIN, OVERBOOKING';
}