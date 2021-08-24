<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractLoggedEvent;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;

class PlayerModifierEvent extends PlayerEvent implements AbstractLoggedEvent, AbstractQuantityEvent
{
    public const ACTION_POINT_MODIFIER = 'action.point.modifier';
    public const MOVEMENT_POINT_MODIFIER = 'movement.point.modifier';
    public const HEALTH_POINT_MODIFIER = 'health.point.modifier';
    public const MORAL_POINT_MODIFIER = 'moral.point.modifier';
    public const SATIETY_POINT_MODIFIER = 'satiety.point.modifier';
    public const MOVEMENT_POINT_CONVERSION = 'movement.point.conversion';

    private int $quantity;
    private string $visibility = VisibilityEnum::PRIVATE;

    public function __construct(
        Player $player,
        int $quantity,
        string $reason,
        \DateTime $time
    ) {
        $this->quantity = $quantity;

        parent::__construct($player, $reason, $time);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): PlayerModifierEvent
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->player->getPlace();
    }
}
