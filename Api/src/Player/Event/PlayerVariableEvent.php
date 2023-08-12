<?php

namespace Mush\Player\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\VariableEventInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerVariableEvent extends PlayerEvent implements LoggableEventInterface, VariableEventInterface
{
    private float $quantity;
    private string $variableName;

    public function __construct(
        Player $player,
        string $variableName,
        float $quantity,
        array $tags,
        \DateTime $time
    ) {
        $this->quantity = $quantity;
        $this->variableName = $variableName;

        parent::__construct($player, $tags, $time);
    }

    public function getRoundedQuantity(): int
    {
        return intval($this->quantity);
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariable(): GameVariable
    {
        return $this->getPlayer()->getVariableByName($this->variableName);
    }

    public function getPlace(): Place
    {
        return $this->getPlayer()->getPlace();
    }

    public function getLogParameters(): array
    {
        $params = parent::getLogParameters();
        $params['quantity'] = abs($this->quantity);

        return $params;
    }
}
