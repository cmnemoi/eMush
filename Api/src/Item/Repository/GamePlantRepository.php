<?php

namespace Mush\Item\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\GameFruit;
use Mush\Item\Entity\GamePlant;

class GamePlantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GamePlant::class);
    }

    public function findOneByName(string $name, Daedalus $daedalus): ?GamePlant
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select( 'game_plant')
            ->from(GamePlant::class, 'game_plant')
            ->leftJoin(GameFruit::class, 'game_fruit', Join::WITH, 'game_plant.gameFruit = game_fruit.id')
            ->where($queryBuilder->expr()->eq('game_fruit.daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->eq('game_plant.name', ':name'))
            ->setParameter('daedalus', $daedalus)
            ->setParameter('name', $name)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}