<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ActionSideEffectsServiceInterface
{
    public function handleActionSideEffect(Action $action, Player $player, ?LogParameterInterface $actionSupport): Player;
}
