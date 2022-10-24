<?php

namespace Mush\Disease\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;

class DiseaseCausesConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiseaseCauseConfig::class);
    }

    public function findCausesByDaedalus(string $cause, Daedalus $daedalus): DiseaseCauseConfig
    {
        $queryBuilder = $this->createQueryBuilder('diseaseCauseConfig');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('diseaseCauseConfig.gameConfig', ':gameConfig'))
            ->andWhere($queryBuilder->expr()->eq('diseaseCauseConfig.causeName', ':name'))
            ->setParameter('gameConfig', $daedalus->getGameConfig())
            ->setParameter('name', $cause)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
