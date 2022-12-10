<?php

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\PlantEffect;

/**
 * @template-extends ServiceEntityRepository<PlantEffect>
 */
class PlantEffectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlantEffect::class);
    }

    public function persist(PlantEffect $plantEffect): PlantEffect
    {
        $this->getEntityManager()->persist($plantEffect);
        $this->getEntityManager()->flush();

        return $plantEffect;
    }
}
