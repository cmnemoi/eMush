<?php

namespace Mush\Communication\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Status\Enum\PlaceStatusEnum;

class MushMessageEnum
{
    public const INFECT_ACTION = 'infect_action';
    public const INFECT_TRAP = 'infect_trap';
    public const INFECT_STD = 'infect_std';
    public const INFECT_CAT = 'infect_cat';
    public const INFECT_MUSH_RAID = 'infect_mush_raid';
    public const INFECT_TRAPPED_RATION = 'infect_trapped_ration';
    public const MUSH_CONVERT_EVENT = 'mush_convert_event';

    public const PLAYER_INFECTION_LOGS = [
        ActionEnum::INFECT->value => self::INFECT_ACTION,
        ActionEnum::DO_THE_THING->value => self::INFECT_STD,
        PlaceStatusEnum::MUSH_TRAPPED->value => self::INFECT_TRAP,
        PlanetSectorEvent::MUSH_TRAP => self::INFECT_MUSH_RAID,
        ActionEnum::CONSUME->value => self::INFECT_TRAPPED_RATION,
        ActionEnum::CONSUME_DRUG->value => self::INFECT_TRAPPED_RATION,
    ];
}
