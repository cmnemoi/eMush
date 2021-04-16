<?php

namespace Mush\Player\Event;

use Mush\Player\Entity\Player;

class PlayerModifierEvent extends PlayerEvent
{
    public const ACTION_POINT_MODIFIER = 'action.point.modifier';
    public const MOVEMENT_POINT_MODIFIER = 'movement.point.modifier';
    public const HEALTH_POINT_MODIFIER = 'health.point.modifier';
    public const MORAL_POINT_MODIFIER = 'moral.point.modifier';
    public const SATIETY_POINT_MODIFIER = 'satiety.point.modifier';

    private int $delta;
    private bool $isDisplayedRoomLog = true;

    public function __construct(Player $player, int $delta, \DateTime $time = null)
    {
        parent::__construct($player, $time);
        $this->delta = $delta;
    }

    public function getDelta(): int
    {
        return $this->delta;
    }

    public function isDisplayedRoomLog(): bool
    {
        return $this->isDisplayedRoomLog;
    }

    public function setIsDisplayedRoomLog(bool $isDisplayedRoomLog): PlayerModifierEvent
    {
        $this->isDisplayedRoomLog = $isDisplayedRoomLog;

        return $this;
    }
}
