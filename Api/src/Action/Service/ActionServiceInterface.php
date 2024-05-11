<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ActionServiceInterface
{
    public function applyCostToPlayer(
        Player $player,
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        ?LogParameterInterface $actionTarget,
        ActionResult $actionResult
    ): Player;

    public function getActionModifiedActionVariable(
        Player $player,
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        ?LogParameterInterface $actionTarget,
        string $variableName
    ): int;

    public function playerCanAffordPoints(
        Player $player,
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        ?LogParameterInterface $actionTarget
    ): bool;
}
