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
final class HunterTargetRepository extends ServiceEntityRepository implements HunterTargetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HunterTarget::class);
    }

    public function findAllBy(array $criteria): array
    {
        return $this->findBy($criteria);
    }

    /** @return HunterTarget[] */
    public function findAllByPatrolShip(GameEquipment $patrolShip): array
    {
        return $this->findBy(['patrolShip' => $patrolShip]);
    }
}
