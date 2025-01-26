<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\ComManagerAnnouncement;
use Mush\Daedalus\Repository\ComManagerAnnouncementRepositoryInterface;
use Mush\Player\Entity\Player;

final readonly class ComManagerAnnouncementService
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
