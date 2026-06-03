<?php

declare(strict_types=1);

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Player\Event\PlayerChangedPlaceEvent;

interface PlayerModifierServiceInterface
{
    public function playerEnterRoom(PlayerChangedPlaceEvent $event): void;

    public function playerLeaveRoom(PlayerChangedPlaceEvent $event): void;
}
