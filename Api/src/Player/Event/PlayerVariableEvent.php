<?php

namespace Mush\Player\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerVariableEvent extends PlayerEvent implements LoggableEventInterface, VariableEventInterface
{
    private int $quantity;
    private string $variableName;

    public function __construct(
        Player $player,
        string $variableName,
        int $quantity,
        array $tags,
        \DateTime $time
    ) {
        $this->quantity = $quantity;
        $this->variableName = $variableName;

        parent::__construct($player, $tags, $time);
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

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariable(): GameVariable
    {
        return $this->getPlayer()->getVariableByName($this->variableName);
    }

    public function getVariableHolder(): GameVariableHolderInterface
    {
        return $this->getPlayer();
    }

    public function getPlace(): Place
    {
        return $this->getPlayer()->getPlace();
    }

    public function getLogParameters(): array
    {
        return [
            $this->getPlayer()->getLogKey() => $this->getPlayer()->getLogName(),
            'quantity' => abs($this->quantity),
        ];
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->player;
    }
}
