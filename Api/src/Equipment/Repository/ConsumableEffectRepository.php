<?php

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\ConsumableEffect;

/**
 * @template-extends ServiceEntityRepository<ConsumableEffect>
 */
class ConsumableEffectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumableEffect::class);
    }

    public function persist(ConsumableEffect $consumableEffect): ConsumableEffect
    {
        $this->getEntityManager()->persist($consumableEffect);
        $this->getEntityManager()->flush();

        return $consumableEffect;
    }

    public function remove(ConsumableEffect $consumableEffect): void
    {
        $this->getEntityManager()->remove($consumableEffect);
        $this->getEntityManager()->flush();
    }
}
