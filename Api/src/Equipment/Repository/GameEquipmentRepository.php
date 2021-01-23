<?php

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;

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
            ->leftJoin('door.rooms', 'room')
            ->andWhere($queryBuilder->expr()->eq('room.daedalus', ':daedalus'))
            ->andWhere('gameEquipment INSTANCE OF ' . Door::class)
            ->distinct()
            ->setParameter(':daedalus', $daedalus)
        ;

        return new ArrayCollection($queryBuilder->getQuery()->getArrayResult());
    }
}
