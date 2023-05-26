<?php

namespace Mush\Place\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;

/**
 * @template-extends ServiceEntityRepository<Place>
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function getPlaceByNameAndDaedalus(string $name, Daedalus $daedalus): ?Place
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.name = :name')
            ->andWhere('p.daedalus = :daedalus')
            ->setParameter('name', $name)
            ->setParameter('daedalus', $daedalus)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
