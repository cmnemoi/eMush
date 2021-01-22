<?php

namespace Mush\Equipment\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Room\Entity\Room;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Door;

class GameEquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameEquipment::class);
    }

    public function findDoorsByDaedalus(Daedalus $daedalus): Collection
    {
        $queryBuilder = $this->createQueryBuilder('gameEquipment');


        $queryBuilder
            ->from(Door::class, 'door')
            ->leftJoin('door.rooms', 'room', Join::WITH)
            ->andWhere($queryBuilder->expr()->eq('room.daedalus', ':daedalus'))
            ->andWhere('gameEquipment INSTANCE OF ' .Door::class)
            ->setParameter(':daedalus', $daedalus)
        ;

        dump($queryBuilder->getQuery()->getArrayResult());
        return new ArrayCollection($queryBuilder->getQuery()->getArrayResult());
    }
}
