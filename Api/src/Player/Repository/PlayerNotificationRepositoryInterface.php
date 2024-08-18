<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\PlayerNotification;

interface PlayerNotificationRepositoryInterface
{
    public function save(PlayerNotification $playerNotification): void;

    public function delete(PlayerNotification $playerNotification): void;
}
