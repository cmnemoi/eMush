<?php

declare(strict_types=1);

namespace Mush\Exploration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Exploration\Entity\PlanetSectorConfig;

/**
 * @template-extends ServiceEntityRepository<PlanetSectorConfig>
 */
class PlanetSectorConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanetSectorConfig::class);
    }

    public function findTotalWeightsAtPlanetGeneration(): int
    {
        $queryBuilder = $this->createQueryBuilder('psc');

        return (int) $queryBuilder
            ->select('SUM(psc.weightAtPlanetGeneration)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
