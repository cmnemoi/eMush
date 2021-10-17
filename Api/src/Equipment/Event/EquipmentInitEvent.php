<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;

class EquipmentInitEvent extends AbstractGameEvent
{
    public const NEW_EQUIPMENT = 'new.equipment';

    private PlaceConfig $placeConfig;

    public function __construct(
        EquipmentConfig $equipmentConfig,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($equipment, $reason, $time);

        $this->equipmentConfig = $equipmentConfig;
    }

    public function getPlaceConfig(): PlaceConfig
    {
        return $this->placeConfig;
    }
}
