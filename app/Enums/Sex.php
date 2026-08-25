<?php

namespace App\Enums;

// use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
// use Filament\Support\Contracts\HasIcon;

enum Sex: string implements HasLabel
{
    case Female = 'female';
    case Male = 'male';
    case Other = 'other';
    case Unknown = 'unknown';
    case Intersex = 'intersex';
    case NotInformed = 'not_informed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Female => 'Mujer',
            self::Male => 'Hombre',
            self::Other => 'Otro',
            self::Unknown => 'Desconocido',
            self::Intersex => 'Intersexual',
            self::NotInformed => 'No informado',
        };
    }
}