<?php

namespace Mush\Action\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class ReportEvent extends Event
{
    public const REPORT_FIRE = 'report.fire';
    public const REPORT_DOOR = 'report.door';
    public const REPORT_EQUIPMENT = 'report.equipment';

    private Player $player;
    private ?GameEquipment $gameEquipment = null;
    private ?Place $place = null;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setGameEquipment(GameEquipment $gameEquipment): ReportEvent
    {
        $this->gameEquipment = $gameEquipment;

        return $this;
    }

    public function getGameEquipment(): ?GameEquipment
    {
        return $this->gameEquipment;
    }

    public function setPlace(Place $place): ReportEvent
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }
}
