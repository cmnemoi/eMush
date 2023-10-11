<?php

declare(strict_types=1);

namespace Mush\Exploration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;
use Mush\Game\Entity\GameConfig;

/**
 * @template-extends ServiceEntityRepository<Planet>
 */
class PlanetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planet::class);
    }

    public function findOneByDaedalusOrienationAndDistance(Daedalus $daedalus, string $orientation, int $distance): ?Planet
    {   
        $queryBuilder = $this->createQueryBuilder('planet');
        $queryBuilder
            ->innerJoin('planet.player', 'player')
            ->where('player.daedalus = :daedalus')
            ->andWhere('planet.orientation = :orientation')
            ->andWhere('planet.distance = :distance')
            ->setParameter('daedalus', $daedalus)
            ->setParameter('orientation', $orientation)
            ->setParameter('distance', $distance)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
