<?php

namespace Mush\Item\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Item;

class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): Item
    {
        $queryBuilder = $this->createQueryBuilder('item');

        $queryBuilder
            ->leftJoin(GameConfig::class, 'gameConfig', Join::WITH, 'gameConfig = item.gameConfig')
            ->leftJoin(
                Daedalus::class,
                'daedalus',
                Join::WITH,
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('daedalus.gameConfig', 'gameConfig.id')
                )
            )
            ->where($queryBuilder->expr()->eq('item.name', ':name'))
            ->setParameter(':daedalus', $daedalus)
            ->setParameter(':name', $name)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
