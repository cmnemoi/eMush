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
        $user = $player->getUser();

        if ($user->lastActivityBefore('-2 days')) {
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
        } elseif ($user->lastActivityBefore('-1 day')) {
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
