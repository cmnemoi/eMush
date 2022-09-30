<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

class ResourcePointChangeEvent extends AbstractModifierHolderEvent
{

    private int $cost;

    public function __construct(Player $player, int $cost, string $reason, \DateTime $time)
    {
        parent::__construct($player, $reason, $time);
        $this->cost = $cost;
    }

    public function addCost(int $cost) : void {
        $this->cost += $cost;
    }

    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

}