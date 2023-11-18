<?php

namespace Mush\RoomLog\Enum;

use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class StatusEventLogEnum
{
    public const SOILED = 'soiled';
    public const BECOME_PREGNANT = 'become_pregnant';
    public const EQUIPMENT_BROKEN = 'equipment_broken';
    public const STUCK_IN_THE_SHIP = 'stuck_in_the_ship';

    public const STATUS_EVENT_LOGS = [
        StatusEvent::STATUS_APPLIED => [
            PlayerStatusEnum::DIRTY => self::SOILED,
            PlayerStatusEnum::PREGNANT => self::BECOME_PREGNANT,
            EquipmentStatusEnum::BROKEN => self::EQUIPMENT_BROKEN,
            PlayerStatusEnum::STUCK_IN_THE_SHIP => self::STUCK_IN_THE_SHIP,
            ],
        StatusEvent::STATUS_REMOVED => [
            EquipmentStatusEnum::PLANT_YOUNG => PlantLogEnum::PLANT_MATURITY,
            ],
        ];
}
