<?php

declare(strict_types=1);

namespace Mush\Status\Service;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;

final class MakePlayerInactiveService
{
    public function __construct(private StatusServiceInterface $statusService) {}

    public function execute(Player $player): void
    {
        if ($player->hasSpentActionPoints()) {
            return;
        }

        if ($player->lastActionIsFromTwoDaysAgoOrLater()) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::INACTIVE,
                holder: $player,
                tags: [],
                time: new \DateTime(),
            );
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
                holder: $player,
                tags: [],
                time: new \DateTime(),
                visibility: VisibilityEnum::PUBLIC
            );
        } elseif ($player->lastActionIsFromYesterdayOrLater()) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::INACTIVE,
                holder: $player,
                tags: [],
                time: new \DateTime(),
                visibility: VisibilityEnum::PUBLIC
            );
        }
    }
}
