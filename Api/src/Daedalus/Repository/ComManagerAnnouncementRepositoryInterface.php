<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\ComManagerAnnouncement;

interface ComManagerAnnouncementRepositoryInterface
{
    public function findByIdOrThrow(int $id): ComManagerAnnouncement;

    public function save(ComManagerAnnouncement $comManagerAnnouncement): void;
}
