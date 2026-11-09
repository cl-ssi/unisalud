<?php

declare(strict_types=1);

namespace App\Enums\ventox;

use Filament\Support\Contracts\HasLabel;

enum Flow: string implements HasLabel
{
    case ZeroPointOne = '0.1';
    case ZeroPointTwo = '0.2';
    case ZeroPointThree = '0.3';
    case ZeroPointFour = '0.4';
    case ZeroPointFive = '0.5';
    case ZeroPointSix = '0.6';
    case ZeroPointSeven = '0.7';
    case ZeroPointEight = '0.8';
    case ZeroPointNine = '0.9';
    case One = '1';
    case Two = '2';
    case Three = '3';
    case Four = '4';
    case Five = '5';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
