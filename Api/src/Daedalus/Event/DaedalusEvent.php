<?php

namespace Mush\Daedalus\Event;

class DaedalusEvent extends DaedalusCycleEvent
{
    public const NEW_DAEDALUS = 'new.daedalus';
    public const START_DAEDALUS = 'start.daedalus';
    public const END_DAEDALUS = 'end.daedalus';
    public const FULL_DAEDALUS = 'full.daedalus';
}
