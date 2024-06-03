<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
 
enum AppointmentStatus: string implements HasLabel
{   
    case Proposed       = 'proposed';
    case Pending        = 'pending';
    case Booked         = 'booked';
    case Arrived        = 'arrived';
    case Fulfilled      = 'fulfilled';
    case Cancelled      = 'cancelled';
    case Noshow         = 'noshow';
    case EnteredInError = 'entered-in-error';
    case CheckedIn      = 'checked-in';
    case Waitlist       = 'waitlist';
    
    public function getLabel(): ?string
    {
        return match ($this) {
            self::Proposed          => 'Propuesta',
            self::Pending           => 'Pendiente',
            self::Booked            => 'Reservada',
            self::Arrived           => 'Llegó',
            self::Fulfilled         => 'Cumplida',
            self::Cancelled         => 'Cancelada',
            self::Noshow            => 'No se presentó',
            self::EnteredInError    => 'Ingresada por error',
            self::CheckedIn         => 'Registrado',
            self::Waitlist          => 'Lista de espera',
        };
    }
}