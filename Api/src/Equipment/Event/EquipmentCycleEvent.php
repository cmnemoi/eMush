<?php

namespace Mush\Equipment\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Collection\ModifierCollection;

class EquipmentCycleEvent extends DaedalusCycleEvent
{
    public const EQUIPMENT_NEW_CYCLE = 'equipment.new.cycle';
    public const EQUIPMENT_NEW_DAY = 'equipment.new.day';

    protected GameEquipment $gameEquipment;

    public function __construct(
        GameEquipment $gameEquipment,
        Daedalus $daedalus,
        array $tags,
        \DateTime $time)
    {
        parent::__construct($daedalus, $tags, $time);

        $this->gameEquipment = $gameEquipment;
    }

    public function getGameEquipment(): GameEquipment
    {
        return $this->gameEquipment;
    }

    public function getModifiers(): ModifierCollection
    {
        $equipment = $this->getGameEquipment();

        $modifiers = $equipment->getAllModifiers()->getEventModifiers($this);

        $player = $this->player;
        if ($player !== null && $equipment->getHolder() !== $player) {
            $modifiers->addModifiers($player->getModifiers()->getEventModifiers($this));
        }

        return $modifiers;
    }
}
