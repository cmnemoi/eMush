<?php

namespace Mush\Status\Service;

use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;

interface StatusServiceInterface
{
    public function createCorePlayerStatus(string $statusName, Player $player);
    public function createCoreItemStatus(string $statusName, GameItem $gameItem);
}
