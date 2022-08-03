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

    public function findCausesByDaedalus(Daedalus $daedalus): DiseaseCauseConfig
    {
        $queryBuilder = $this->createQueryBuilder('diseaseCauseConfig');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('diseaseCauseConfig.gameConfig', ':gameConfig'))
            ->setParameter('gameConfig', $daedalus->getGameConfig())
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
