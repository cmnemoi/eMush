<?php

namespace Mush\Status\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Entity\Config\StatusConfig;

class StatusConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusConfig::class);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?StatusConfig
    {
        $queryBuilder = $this->createQueryBuilder('statusConfig');

        $queryBuilder
            ->leftJoin(GameConfig::class, 'gameConfig', Join::WITH, 'gameConfig = statusConfig.gameConfig')
            ->leftJoin(
                Daedalus::class,
                'daedalus',
                Join::WITH,
                'daedalus.gameConfig = gameConfig.id'
            )
            ->where($queryBuilder->expr()->eq('daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->eq('statusConfig.name', ':name'))
            ->setParameter(':daedalus', $daedalus)
            ->setParameter(':name', $name)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findByDaedalus(Daedalus $daedalus): ArrayCollection
    {
        $queryBuilder = $this->createQueryBuilder('statusConfig');

        $queryBuilder
            ->leftJoin(GameConfig::class, 'gameConfig', Join::WITH, 'gameConfig = statusConfig.gameConfig')
            ->leftJoin(
                Daedalus::class,
                'daedalus',
                Join::WITH,
                'daedalus.gameConfig = gameConfig.id'
            )
            ->where($queryBuilder->expr()->eq('daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->in(':equipmentName', 'statusConfig.applyToEquipments'))
            ->setParameter(':daedalus', $daedalus)
            ->setMaxResults(1)
        ;

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
