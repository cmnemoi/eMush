<?php

namespace Mush\Status\Service;

use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;

class StatusService implements StatusServiceInterface
{
    public function createCorePlayerStatus(string $statusName, Player $player) {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setPlayer($player)
        ;
    }
    public function createCoreItemStatus(string $statusName, GameItem $gameItem) {
        $status = new Status();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setGameItem($gameItem)
        ;
    }

    public function createAttemptStatus(string $statusName, string $action, Player $player) {
        $status = new Attempt();
        $status
            ->setName($statusName)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setPlayer($player)
            ->setAction($action)
            ->setCharge(0)
        ;
    }
}
