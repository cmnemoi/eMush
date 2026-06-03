<?php

declare(strict_types=1);

namespace Mush\Place\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Place\Entity\PlaceConfig;

/**
 * @template-extends ServiceEntityRepository<PlaceConfig>
 */
class PlaceConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaceConfig::class);
    }
}
