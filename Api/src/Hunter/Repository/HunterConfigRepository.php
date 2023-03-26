<?php

namespace Mush\Hunter\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Hunter\Entity\HunterConfig;

/**
 * @template-extends ServiceEntityRepository<HunterConfig>
 */
class HunterConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HunterConfig::class);
    }
}
