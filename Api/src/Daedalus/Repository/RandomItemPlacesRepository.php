<?php

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\RandomItemPlaces;

/**
 * @template-extends ServiceEntityRepository<RandomItemPlaces>
 */
class RandomItemPlacesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RandomItemPlaces::class);
    }
}
