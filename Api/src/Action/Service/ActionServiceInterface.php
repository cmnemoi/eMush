<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;

interface ActionServiceInterface
{
    public function canPlayerDoAction(Player $player, Action $action): bool;

    public function applyCostToPlayer(Player $player, Action $action): Player;

    public function getTotalActionPointCost(Player $player, Action $action): int;

    public function getTotalMovementPointCost(Player $player, Action $action): int;

    public function getTotalMoralPointCost(Player $player, Action $action): int;

    public function getSuccessRate(
        Action $action,
        Player $player
    ): int;

    public function getAttempt(Player $player, string $actionName): Attempt;
}
