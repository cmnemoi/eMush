<?php

namespace Mush\Action\Service;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;

interface ActionServiceInterface
{
    public function canPlayerDoAction(AbstractAction $action): bool;

    public function applyCostToPlayer(AbstractAction $action): Player;

    public function getTotalActionPointCost(Player $player, Action $action, ?ActionParameter $parameter): int;

    public function getTotalMovementPointCost(Player $player, Action $action, ?ActionParameter $parameter): int;

    public function getTotalMoralPointCost(Player $player, Action $action, ?ActionParameter $parameter): int;

    public function getSuccessRate(
        Action $action,
        Player $player,
        ?ActionParameter $parameter
    ): int;

    public function getAttempt(Player $player, string $actionName): Attempt;
}
