<?php

namespace Mush\Modifier\Service;

use Mush\Player\Entity\Player;

interface PlayerModifierServiceInterface
{
    public function playerEnterRoom(Player $player): void;

    public function playerLeaveRoom(Player $player): void;
}
