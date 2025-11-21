<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Repository\PlayerNotificationRepositoryInterface;

final readonly class UpdatePlayerNotificationService
{
    public function __construct(private PlayerNotificationRepositoryInterface $playerNotificationRepository) {}

    public function execute(Player $player, PlayerNotificationEnum $message, array $parameters = []): void
    {
        if ($player->hasNotificationByMessage($message)) {
            $this->playerNotificationRepository->delete($player->getNotificationByMessageOrThrow($message));
        }

        $this->playerNotificationRepository->save(new PlayerNotification($player, $message, $parameters));
    }
}
