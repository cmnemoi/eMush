<?php

namespace Mush\Equipment\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Config\GameEquipment;

class EquipmentCycleEvent extends DaedalusCycleEvent
{
    public const EQUIPMENT_NEW_CYCLE = 'equipment.new.cycle';
    public const EQUIPMENT_NEW_DAY = 'equipment.new.day';

    private GameEquipment $gameEquipment;

    public function __construct(
        GameEquipment $gameEquipment,
        Daedalus $daedalus,
        string $reason,
        \DateTime $time)
    {
        parent::__construct($daedalus, $reason, $time);

        $this->gameEquipment = $gameEquipment;
    }

    public function getGameEquipment(): GameEquipment
    {
        return $this->gameEquipment;
    }
}
