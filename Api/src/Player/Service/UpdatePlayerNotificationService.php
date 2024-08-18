<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Repository\PlayerNotificationRepositoryInterface;

final class UpdatePlayerNotificationService
{
    public function __construct(
        private PlayerNotificationRepositoryInterface $playerNotificationRepository,
    ) {}

    public function execute(Player $player, string $message): void
    {
        if ($player->hasNotification()) {
            $this->playerNotificationRepository->delete($player->getNotificationOrThrow());
        }

        $this->playerNotificationRepository->save(new PlayerNotification($player, $message));
    }
}
