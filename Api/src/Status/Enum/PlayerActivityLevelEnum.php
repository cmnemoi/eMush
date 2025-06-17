<?php

namespace Mush\Status\Enum;

/**
 * Class enumerating the possible Activity Levels that can be displayed with the CheckRoster action.
 * These activity levels are only for informative purposes.
 *
 * AWAKE = The Character is alive on the current game.
 * IDLE = The Character is alive but has not performed any action recently on the current game.
 * DEAD = The Character has died on the current game.
 * CRYOGENIZED = Default status for an unknown Character that has not been selected on the current game.
 */
enum PlayerActivityLevelEnum: string
{
    case AWAKE = 'awake';
    case IDLE = 'idle';
    case DEAD = 'dead';
    case CRYOGENIZED = 'cryogenized';

    public function toString(): string
    {
        return $this->value;
    }
}
