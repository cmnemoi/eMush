<?php

namespace Mush\Player\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Player\Entity\Player;

class ResourcePointChangeEvent extends AbstractModifierHolderEvent
{
    public const CHECK_CHANGE_MORAL_POINT = 'check_change_moral_point';
    public const CHECK_CHANGE_ACTION_POINT = 'check_change_action_point';
    public const CHECK_CHANGE_MOVEMENT_POINT = 'check_change_movement_point';
    public const CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_COST = 'check_conversion_action_to_movement_point_cost';
    public const CHECK_CONVERSION_ACTION_TO_MOVEMENT_POINT_GAIN = 'check_conversion_action_to_movement_point_gain';

    private int $cost;
    private string $variablePoint;
    private bool $consumed;

    public function __construct(
        Player $player,
        string $variablePoint,
        int $cost,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($player, $reason, $time);
        $this->cost = $cost;
        $this->variablePoint = $variablePoint;
        $this->consumed = false;
    }

    public function setConsumed(bool $consumed): void
    {
        $this->consumed = $consumed;
    }

    public function isConsumed(): bool
    {
        return $this->consumed;
    }

    public function addCost(int $cost): void
    {
        $this->cost += $cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    public function getVariablePoint(): string
    {
        return $this->variablePoint;
    }

    public function getCost(): int
    {
        return $this->cost;
    }
}
