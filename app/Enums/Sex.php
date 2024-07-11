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

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Female => 'Mujer',
            self::Male => 'Hombre',
            self::Other => 'Otro',
            self::Unknown => 'Desconocido',
        };
    }
}