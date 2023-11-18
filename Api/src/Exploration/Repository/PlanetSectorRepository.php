<?php

declare(strict_types=1);

namespace Mush\Exploration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Exploration\Entity\PlanetSector;

/**
 * @template-extends ServiceEntityRepository<PlanetSector>
 */
class PlanetSectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanetSector::class);
    }
}
