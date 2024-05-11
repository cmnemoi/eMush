<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ActionSideEffectsServiceInterface
{
    public function handleActionSideEffect(
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        Player $player,
        ?LogParameterInterface $actionTarget
    ): Player;
}
