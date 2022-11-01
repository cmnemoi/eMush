<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerVariableEvent extends PlayerEvent implements LoggableEventInterface, AbstractQuantityEvent
{
    public const ACTION_COST = 'action_cost';
    public const CONVERT_ACTION_TO_MOVEMENT_POINT = 'convert_action_to_movement_point';

    private int $quantity;
    private string $modifiedVariable;
    private bool $modified = true;
    private bool $artificial = false;
    private bool $ignored;

    public function __construct(
        Player $player,
        string $modifiedVariable,
        int $quantity,
        string $reason,
        \DateTime $time,
        bool $ignored = false
    ) {
        $this->quantity = $quantity;
        $this->modifiedVariable = $modifiedVariable;
        $this->ignored = $ignored;

        parent::__construct($player, $reason, $time);
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

    public function setModified(bool $modified): void
    {
        $this->modified = $modified;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function isArtificial(): bool
    {
        return $this->artificial;
    }

    public function setArtificial(bool $artificial): void
    {
        $this->artificial = $artificial;
    }

    public function isIgnored(): bool
    {
        return $this->ignored;
    }
}
