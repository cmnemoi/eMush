<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Player\Entity\Player;

interface ActionServiceInterface
{
    public function applyCostToPlayer(Player $player, Action $action, ?ActionParameter $parameter): Player;

    public function getTotalActionPointCost(Player $player, Action $action, ?ActionParameter $parameter): int;

    public function getTotalMovementPointCost(Player $player, Action $action, ?ActionParameter $parameter): int;

    public function getTotalMoralPointCost(Player $player, Action $action, ?ActionParameter $parameter): int;

    public function getSuccessRate(
        Action $action,
        Player $player,
        ?ActionParameter $parameter
    ): int;
}
