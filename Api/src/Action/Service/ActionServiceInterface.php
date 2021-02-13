<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;

interface ActionServiceInterface
{
    public function canPlayerDoAction(Player $player, Action $action): bool;

    public function applyCostToPlayer(Player $player, Action $action): Player;

    public function getTotalActionPointCost(Player $player, Action $action): int;

    public function getSuccessRate(
        Action $action,
        int $baseRate,
        int $numberOfAttempt,
        float $relativeModificator,
    ): int;
}
