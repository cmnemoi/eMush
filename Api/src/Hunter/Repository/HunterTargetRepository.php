<?php

declare(strict_types=1);

namespace Mush\Hunter\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Entity\HunterTarget;

/**
 * @template-extends ServiceEntityRepository<HunterTarget>
 */
final class HunterTargetRepository extends ServiceEntityRepository implements HunterTargetRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HunterTarget::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function delete(HunterTarget $hunterTarget): void
    {
        $this->entityManager->remove($hunterTarget);
        $this->entityManager->flush();
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
