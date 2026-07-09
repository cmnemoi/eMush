<?php

declare(strict_types=1);

namespace Mush\Exploration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Exploration\Entity\ClosedExploration;
use Symfony\Component\Uid\Uuid;

/**
 * @template-extends ServiceEntityRepository<ClosedExploration>
 */
class ClosedExplorationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClosedExploration::class);
    }

    public function existsByUuid(string $uuid): bool
    {
        return $this->count(['uuid' => $uuid]) > 0;
    }

    public function generateUniqueUuid(): string
    {
        do {
            $uuid = Uuid::v4()->toRfc4122();
        } while ($this->existsByUuid($uuid));

        return $uuid;
    }
}
