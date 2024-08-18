<?php

declare(strict_types=1);

namespace Mush\Player\UseCase;

use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Repository\PlayerNotificationRepositoryInterface;

final class DeletePlayerNotificationUseCase
{
    public function __construct(
        private PlayerNotificationRepositoryInterface $playerNotificationRepository,
    ) {}

    public function execute(PlayerNotification $playerNotification): void
    {
        $this->playerNotificationRepository->delete($playerNotification);
    }
}
