<?php

namespace Mush\Status\Service;

use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;

interface StatusServiceInterface
{
    public function createCorePlayerStatus(string $statusName, Player $player): Status;

    public function createCoreItemStatus(string $statusName, GameItem $gameItem): Status;

    public function createAttemptStatus(string $statusName, string $action, Player $player): Attempt;
}
