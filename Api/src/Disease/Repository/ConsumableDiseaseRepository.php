<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Disease\Entity\ConsumableDisease;

/**
 * @template-extends ServiceEntityRepository<ConsumableDisease>
 */
class ConsumableDiseaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumableDisease::class);
    }
}
