<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;

class EquipmentInitEvent extends AbstractGameEvent
{
    public const NEW_EQUIPMENT = 'new.equipment';

    private EquipmentConfig $equipmentConfig;
    private GameEquipment $gameEquipment;

    public function __construct(
        GameEquipment $gameEquipment,
        EquipmentConfig $equipmentConfig,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($reason, $time);

        $this->equipmentConfig = $equipmentConfig;
        $this->gameEquipment = $gameEquipment;
    }

    public function getEquipmentConfig(): EquipmentConfig
    {
        return $this->equipmentConfig;
    }

    public function getGameEquipment(): GameEquipment
    {
        return $this->gameEquipment;
    }
}
