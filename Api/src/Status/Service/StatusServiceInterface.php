<?php

namespace Mush\Status\Service;

use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;

interface StatusServiceInterface
{
    public function createCorePlayerStatus(string $statusName, Player $player): Status;

    public function createCoreItemStatus(string $statusName, GameItem $gameItem): Status;

    public function createChargeItemStatus(
        string $statusName,
        GameItem $gameItem,
        string $strategy,
        int $charge = 0,
        int $threshold = null
    ): ChargeStatus;

    public function createAttemptStatus(string $statusName, string $action, Player $player): Attempt;
}
