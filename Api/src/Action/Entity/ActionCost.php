<?php

namespace Mush\Action\Entity;

use Mush\Player\Entity\Player;

class ActionCost
{
    private int $actionPointCost = 0;
    private int $movementPointCost = 0;
    private int $moralPointCost = 0;

    public function canPlayerDoAction(Player $player): bool
    {
        return $this->getActionPointCost() <= $player->getActionPoint() &&
            ($this->getMovementPointCost() <= $player->getMovementPoint() || $player->getActionPoint() > 0) &&
            $this->getMoralPointCost() <= $player->getMoralPoint()
            ;
    }

    public function applyCostToPlayer(Player $player): Player
    {
        return $player
            ->addActionPoint((-1) * $this->getActionPointCost())
            ->addMovementPoint((-1) * $this->getMovementPointCost())
            ->addMoralPoint((-1) * $this->getMoralPointCost())
            ;
    }

    public function getActionPointCost(): ?int
    {
        return $this->actionPointCost;
    }

    public function setActionPointCost(int $actionPointCost): ActionCost
    {
        $this->actionPointCost = $actionPointCost;
        return $this;
    }

    public function getMovementPointCost(): ?int
    {
        return $this->movementPointCost;
    }

    public function setMovementPointCost(int $movementPointCost): ActionCost
    {
        $this->movementPointCost = $movementPointCost;
        return $this;
    }

    public function getMoralPointCost(): ?int
    {
        return $this->moralPointCost;
    }

    public function setMoralPointCost(int $moralPointCost): ActionCost
    {
        $this->moralPointCost = $moralPointCost;
        return $this;
    }
}
