<?php

declare(strict_types=1);

namespace Mush\Hunter\Event;

class HunterCycleEvent extends AbstractHunterEvent
{
    public const HUNTER_NEW_CYCLE = 'hunter.new.cycle';
}
