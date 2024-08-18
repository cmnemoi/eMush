<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerNotification;

final class InMemoryPlayerNotificationRepository implements PlayerNotificationRepositoryInterface
{
    private array $playerNotifications = [];

    public function save(PlayerNotification $playerNotification): void
    {
        $this->playerNotifications[$playerNotification->getPlayer()->getId()] = $playerNotification;
    }

    public function delete(PlayerNotification $playerNotification): void
    {
        $playerNotification->getPlayer()->deleteNotification();
        unset($this->playerNotifications[$playerNotification->getPlayer()->getId()]);
    }

    public function clear(): void
    {
        $this->playerNotifications = [];
    }

    public function findByPlayer(Player $player): ?PlayerNotification
    {
        return $this->playerNotifications[$player->getId()] ?? null;
    }
}
