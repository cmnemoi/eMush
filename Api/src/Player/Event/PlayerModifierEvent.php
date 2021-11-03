<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerModifierEvent extends PlayerEvent implements LoggableEventInterface, AbstractQuantityEvent
{
    public const ACTION_POINT_MODIFIER = 'action.point.modifier';
    public const MOVEMENT_POINT_MODIFIER = 'movement.point.modifier';
    public const HEALTH_POINT_MODIFIER = 'health.point.modifier';
    public const MORAL_POINT_MODIFIER = 'moral.point.modifier';
    public const SATIETY_POINT_MODIFIER = 'satiety.point.modifier';

    private int $quantity;

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

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->player->getPlace();
    }

    public function getLogParameters(): array
    {
        return [
            $this->player->getLogKey() => $this->player->getLogName(),
            'quantity' => $this->quantity,
        ];
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->player;
    }
}
