<?php

declare(strict_types=1);

namespace Mush\Equipment\Enum;

enum WeaponEventType: string
{
    case NORMAL = 'normal';
    case MISS = 'miss';
    case CRITIC = 'critic';
    case FUMBLE = 'fumble';
    case NULL = '';

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(WeaponEventType $other): bool
    {
        return $this->value === $other->value;
    }
}
