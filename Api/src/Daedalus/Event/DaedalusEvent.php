<?php

namespace Mush\Daedalus\Event;

class DaedalusEvent extends DaedalusCycleEvent
{
    public const string START_DAEDALUS = 'start.daedalus';
    public const string FINISH_DAEDALUS = 'finish.daedalus';
    public const string FULL_DAEDALUS = 'full.daedalus';
    public const string DELETE_DAEDALUS = 'delete.daedalus';
    public const string TRAVEL_LAUNCHED = 'travel.launched';
    public const string TRAVEL_FINISHED = 'travel.finished';
    public const string CHANGED_ORIENTATION = 'changed.orientation';
}
