<?php

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Player\Entity\Player;

interface PlayerModifierServiceInterface
{
    public function playerEnterRoom(Player $player, array $tags, \DateTime $time): void;

    public function playerLeaveRoom(Player $player, array $tags, \DateTime $time): void;
}
