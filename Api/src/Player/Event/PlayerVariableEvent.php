<?php

namespace Mush\Player\Event;

use Mush\Game\Event\QuantityEventInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerVariableEvent extends PlayerEvent implements LoggableEventInterface, QuantityEventInterface
{
    private int $quantity;
    private string $modifiedVariable;

    public function __construct(
        Player $player,
        string $modifiedVariable,
        int $quantity,
        array $tags,
        \DateTime $time
    ) {
        $this->quantity = $quantity;
        $this->modifiedVariable = $modifiedVariable;

        parent::__construct($player, $tags, $time);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getModifiedVariable(): string
    {
        return $this->modifiedVariable;
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
            'quantity' => abs($this->quantity),
        ];
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->player;
    }
}
