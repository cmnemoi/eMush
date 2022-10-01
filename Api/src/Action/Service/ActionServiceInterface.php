<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ActionServiceInterface
{
    public function applyCostToPlayer(Player $player, Action $action, ?LogParameterInterface $parameter): Player;

    public function getTotalActionPointCost(Player $player, Action $action,): int;

    public function getTotalMovementPointCost(Player $player, Action $action): int;

    public function getTotalMoralPointCost(Player $player, Action $action): int;

    public function getSuccessRate(Action $action, Player $player,): int;
}
