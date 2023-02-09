<?php

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanics;

/**
 * @template-extends ServiceEntityRepository<EquipmentMechanic>
 */
class MechanicsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mechanics::class);
    }
}
