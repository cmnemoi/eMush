<?php

declare(strict_types=1);

namespace Mush\Hunter\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Entity\HunterTarget;

/**
 * @template-extends ServiceEntityRepository<HunterTarget>
 */
final class HunterTargetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HunterTarget::class);
    }

    /** @return HunterTarget[] */
    public function findAllByPatrolShip(GameEquipment $patrolShip): array
    {
        return $this->findBy(['patrolShip' => $patrolShip]);
    }
}
