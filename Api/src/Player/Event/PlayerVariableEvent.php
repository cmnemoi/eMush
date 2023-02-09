<?php

namespace Mush\Player\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Event\ModifiableEventInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerVariableEvent extends PlayerEvent implements LoggableEventInterface, VariableEventInterface, ModifiableEventInterface
{
    private int $quantity;
    private string $variableName;

    public function __construct(
        Player    $player,
        string    $variableName,
        int       $quantity,
        array     $tags,
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
        return $this->player->getVariableByName($this->variableName);
    }

    public function getVariableHolder(): GameVariableHolderInterface
    {
        return $this->player;
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

    public function getModifiers(): ModifierCollection
    {
        return $this->player->getAllModifiers();
    }
}
