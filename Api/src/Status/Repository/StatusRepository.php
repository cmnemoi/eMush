<?php

namespace Mush\Status\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusTarget;

class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function findStatusTargetingGameEquipment(GameEquipment $gameEquipment, string $statusName): ?Status
    {
        $qb = $this->createQueryBuilder('s');

        $qb
            ->leftJoin(StatusTarget::class, 'st', Join::WITH, 'st.status = s.id')
            ->where($qb->expr()->eq('s.name', ':statusName'))
            ->andWhere($qb->expr()->eq('st.gameEquipment', ':equipment'))
            ->setParameter('statusName', $statusName)
            ->setParameter('equipment', $gameEquipment)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
