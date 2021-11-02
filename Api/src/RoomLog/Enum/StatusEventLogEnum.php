<?php

namespace Mush\RoomLog\Enum;

use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class StatusEventLogEnum
{
    public const SOILED = 'soiled';
    public const HUNGER = 'hunger';
    public const BECOME_PREGNANT = 'become_pregnant';
    public const EQUIPMENT_BROKEN = 'equipment_broken';

    public const STATUS_EVENT_LOGS = [
        StatusEvent::STATUS_APPLIED => [
            PlayerStatusEnum::DIRTY => self::SOILED,
            PlayerStatusEnum::STARVING => self::HUNGER,
            PlayerStatusEnum::PREGNANT => self::BECOME_PREGNANT,
            EquipmentStatusEnum::BROKEN => self::EQUIPMENT_BROKEN,
            ],
        StatusEvent::STATUS_REMOVED => [
            EquipmentStatusEnum::PLANT_YOUNG => PlantLogEnum::PLANT_MATURITY,
            ],
        ];
}
