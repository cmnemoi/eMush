<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Player\Entity\Player;

class ResourceMaxPointEvent extends AbstractModifierHolderEvent
{
    public const CHECK_MAX_POINT = 'check_max_point';

    private int $value;
    private string $variablePoint;

    public function __construct(Player $player, string $variablePoint, int $value, string $reason, \DateTime $time)
    {
        parent::__construct($player, $reason, $time);
        $this->value = $value;
        $this->variablePoint = $variablePoint;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getVariablePoint(): string
    {
        return $this->variablePoint;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
