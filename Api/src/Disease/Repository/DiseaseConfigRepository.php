<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Disease\Entity\Config\DiseaseConfig;

/**
 * @template-extends ServiceEntityRepository<DiseaseConfig>
 */
class DiseaseConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiseaseConfig::class);
    }
}
