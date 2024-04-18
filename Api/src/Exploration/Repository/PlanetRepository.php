<?php

declare(strict_types=1);

namespace Mush\Exploration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;

/**
 * @template-extends ServiceEntityRepository<Planet>
 */
class PlanetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planet::class);
    }

    public function findAllByDaedalus(Daedalus $daedalus): array
    {
        $queryBuilder = $this->createQueryBuilder('planet');
        $queryBuilder
            ->innerJoin('planet.player', 'player')
            ->where('player.daedalus = :daedalus')
            ->setParameter('daedalus', $daedalus);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findOneByDaedalusDestination(Daedalus $daedalus): ?Planet
    {
        $queryBuilder = $this->createQueryBuilder('planet');
        $queryBuilder
            ->innerJoin('planet.player', 'player')
            ->where('player.daedalus = :daedalus')
            ->andWhere('planet.distance = :distance')
            ->andWhere('planet.orientation = :orientation')
            ->setParameter('daedalus', $daedalus)
            ->setParameter('distance', $daedalus->getDestination()->getDistance())
            ->setParameter('orientation', $daedalus->getDestination()->getOrientation());

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
