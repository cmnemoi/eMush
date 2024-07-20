<?php

declare(strict_types=1);

namespace Mush\Status\Service;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;

final class MakePlayerActiveService
{
    public function __construct(private StatusServiceInterface $statusService) {}

    public function execute(Player $player): void
    {
        if ($player->lastActionIsFromYesterdayOrLater()) {
            return;
        }

        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $player,
            tags: [],
            time: new \DateTime(),
            visibility: VisibilityEnum::PUBLIC,
        );
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $player,
            tags: [],
            time: new \DateTime(),
            visibility: VisibilityEnum::PUBLIC,
        );
    }
}
