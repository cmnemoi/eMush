<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Player\Entity\ComManagerAnnouncement;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\ComManagerAnnouncementRepositoryInterface;

final readonly class AddComManagerAnnouncementToPlayerService
{
    public function __construct(private ComManagerAnnouncementRepositoryInterface $comManagerAnnouncementRepository) {}

    public function execute(Player $comManager, string $announcement): void
    {
        $this->comManagerAnnouncementRepository->save(
            new ComManagerAnnouncement(
                comManager: $comManager,
                announcement: $announcement,
            )
        );
    }
}
