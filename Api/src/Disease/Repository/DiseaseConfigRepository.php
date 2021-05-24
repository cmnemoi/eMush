<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\DiseaseConfig;

class DiseaseConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiseaseConfig::class);
    }

    public function findByCauses(string $cause, Daedalus $daedalus): array
    {
        $queryBuilder = $this->createQueryBuilder('diseaseConfig');

        $queryBuilder
            ->join('diseaseConfig.causes', 'cause')
            ->where($queryBuilder->expr()->eq('diseaseConfig.gameConfig', ':gameConfig'))
            ->andWhere($queryBuilder->expr()->eq('cause.name', ':cause'))
            ->setParameter('gameConfig', $daedalus->getGameConfig())
            ->setParameter('cause', $cause)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
