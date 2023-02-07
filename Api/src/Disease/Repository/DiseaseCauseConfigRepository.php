<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;

/**
 * @template-extends ServiceEntityRepository<DiseaseCauseConfig>
 */
class DiseaseCauseConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiseaseCauseConfig::class);
    }
}
