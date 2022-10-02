<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseConfig;

class DiseaseConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiseaseConfig::class);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): DiseaseConfig
    {
        $queryBuilder = $this->createQueryBuilder('diseaseConfig');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('diseaseConfig.gameConfig', ':gameConfig'))
            ->andWhere($queryBuilder->expr()->eq('diseaseConfig.name', ':name'))
            ->setParameter('gameConfig', $daedalus->getGameConfig())
            ->setParameter(':name', $name)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
