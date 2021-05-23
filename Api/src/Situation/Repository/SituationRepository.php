<?php

namespace Mush\Situation\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Situation\Entity\Situation;

class SituationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Situation::class);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): Situation
    {
        $queryBuilder = $this->createQueryBuilder('situation');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->eq('name', ':name'))
            ->setParameter(':daedalus', $daedalus)
            ->setParameter(':name', $name)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
