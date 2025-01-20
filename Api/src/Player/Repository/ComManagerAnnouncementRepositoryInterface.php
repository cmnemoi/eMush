<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Player\Entity\ComManagerAnnouncement;

interface ComManagerAnnouncementRepositoryInterface
{
    public function findByIdOrThrow(int $id): ComManagerAnnouncement;

    public function save(ComManagerAnnouncement $comManagerAnnouncement): void;
}
