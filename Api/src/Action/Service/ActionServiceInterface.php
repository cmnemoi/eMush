<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ActionServiceInterface
{
    public function applyCostToPlayer(Player $player, Action $action, ?LogParameterInterface $actionTarget): Player;

    public function getActionModifiedActionVariable(
        Player $player,
        Action $action,
        ?LogParameterInterface $actionTarget,
        string $variableName
    ): int;

    public function playerCanAffordPoints(Player $player, Action $action, ?LogParameterInterface $actionTarget): bool;
}
