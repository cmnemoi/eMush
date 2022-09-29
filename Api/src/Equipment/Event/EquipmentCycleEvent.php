<?php

namespace Mush\Equipment\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Equipment;

class EquipmentCycleEvent extends DaedalusCycleEvent
{
    public const EQUIPMENT_NEW_CYCLE = 'equipment.new.cycle';
    public const EQUIPMENT_NEW_DAY = 'equipment.new.day';

    private Equipment $gameEquipment;

    public function __construct(
        Equipment $gameEquipment,
        Daedalus  $daedalus,
        string    $reason,
        \DateTime $time)
    {
        parent::__construct($daedalus, $reason, $time);

        $this->gameEquipment = $gameEquipment;
    }

    public function getGameEquipment(): Equipment
    {
        return $this->gameEquipment;
    }
}
