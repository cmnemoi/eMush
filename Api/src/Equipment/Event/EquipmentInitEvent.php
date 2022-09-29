<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Game\Event\AbstractGameEvent;

class EquipmentInitEvent extends AbstractGameEvent
{
    public const NEW_EQUIPMENT = 'new.equipment';

    private EquipmentConfig $equipmentConfig;
    private Equipment $gameEquipment;

    public function __construct(
        Equipment       $gameEquipment,
        EquipmentConfig $equipmentConfig,
        string          $reason,
        \DateTime       $time
    ) {
        parent::__construct($reason, $time);

        $this->equipmentConfig = $equipmentConfig;
        $this->gameEquipment = $gameEquipment;
    }

    public function getEquipmentConfig(): EquipmentConfig
    {
        return $this->equipmentConfig;
    }

    public function getGameEquipment(): Equipment
    {
        return $this->gameEquipment;
    }
}
