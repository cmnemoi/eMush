<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Mush\Daedalus\Entity\ComManagerAnnouncement;

final class InMemoryComManagerAnnouncementRepository implements ComManagerAnnouncementRepositoryInterface
{
    private array $comManagerAnnouncements = [];

    public function findByIdOrThrow(int $id): ComManagerAnnouncement
    {
        return array_filter($this->comManagerAnnouncements, static fn (ComManagerAnnouncement $comManagerAnnouncement) => $comManagerAnnouncement->getId() === $id)[0] ?? throw new \RuntimeException("ComManagerAnnouncement {$id} not found");
    }

    public function save(ComManagerAnnouncement $comManagerAnnouncement): void
    {
        $comManagerAnnouncement->setCreatedAt(new \DateTime());
        (new \ReflectionProperty(ComManagerAnnouncement::class, 'id'))->setValue($comManagerAnnouncement, \count($this->comManagerAnnouncements) + 1);
        $this->comManagerAnnouncements[] = $comManagerAnnouncement;
    }

    public function clear(): void
    {
        $this->comManagerAnnouncements = [];
    }

    public function findByComManagerAndAnnouncement(int $comManagerId, string $announcement): array
    {
        return array_filter($this->comManagerAnnouncements, static function (ComManagerAnnouncement $comManagerAnnouncement) use ($comManagerId, $announcement) {
            return $comManagerAnnouncement->getComManager()->getId() === $comManagerId
                && $comManagerAnnouncement->getAnnouncement() === $announcement;
        });
    }
}
