<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Disease\Entity\Config\SymptomConfig;

/**
 * @template-extends ServiceEntityRepository<SymptomConfig>
 */
class SymptomConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SymptomConfig::class);
    }
}
