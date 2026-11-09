<?php

declare(strict_types=1);

namespace App\Enums\ventox;

use Filament\Support\Contracts\HasLabel;

enum TherapyTime: string implements HasLabel
{
    case TwelveMinutes = '12';
    case FifteenMinutes = '15';
    case EighteenMinutes = '18';
    case TwentyFourMinutes = '24';
    case NightShift = 'night_shift';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::TwelveMinutes => '12 min',
            self::FifteenMinutes => '15 min',
            self::EighteenMinutes => '18 min',
            self::TwentyFourMinutes => '24 min',
            self::NightShift => 'Turno nocturno',
        };
    }
}
