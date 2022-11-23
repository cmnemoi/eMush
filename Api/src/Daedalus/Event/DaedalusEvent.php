<?php

namespace Mush\Daedalus\Event;

class DaedalusEvent extends DaedalusCycleEvent
{
    public const START_DAEDALUS = 'start.daedalus';
    public const FINISH_DAEDALUS = 'finish.daedalus';
    public const FULL_DAEDALUS = 'full.daedalus';
    public const CLOSE_DAEDALUS = 'close.daedalus';
}
