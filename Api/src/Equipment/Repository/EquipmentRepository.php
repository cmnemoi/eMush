<?php

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Game\Entity\GameConfig;

class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentConfig::class);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): EquipmentConfig
    {
        $queryBuilder = $this->createQueryBuilder('equipment');

        $queryBuilder
            ->leftJoin(GameConfig::class, 'gameConfig', Join::WITH, 'gameConfig = equipment.gameConfig')
            ->leftJoin(
                Daedalus::class,
                'daedalus',
                Join::WITH,
                'daedalus.gameConfig = gameConfig.id'
            )
            ->where($queryBuilder->expr()->eq('daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->eq('equipment.name', ':name'))
            ->setParameter(':daedalus', $daedalus)
            ->setParameter(':name', $name)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
