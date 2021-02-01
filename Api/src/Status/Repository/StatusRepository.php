<?php

namespace Mush\Status\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusTarget;

class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function findByCriteria(StatusCriteria $criteria): array
    {
        $queryBuilder = $this->createQueryBuilder('status');

        $queryBuilder
            ->join(StatusTarget::class, 'status_target', Join::WITH, 'status_target = status.owner')
            ->leftJoin(Player::class, 'player', Join::WITH, 'player = status_target.player')
            ->leftJoin(Room::class, 'room', Join::WITH, 'room = status_target.room')
            ->leftJoin(GameEquipment::class, 'equipment', Join::WITH, 'equipment = status_target.gameEquipment')
            ->leftJoin(GameItem::class, 'item', Join::WITH, 'item = status_target.gameEquipment')
            ->leftJoin(Player::class, 'item_player', Join::WITH, 'item.player = item_player')
            ->leftJoin(Room::class, 'item_room', Join::WITH, 'item.room = item_room')
            ->leftJoin(Room::class, 'equipment_room', Join::WITH, 'equipment.room = equipment_room')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('player.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('room.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('item_player.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('item_room.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('equipment_room.daedalus', ':daedalus')
                )
            )
            ->setParameter('daedalus', $criteria->getDaedalus())
        ;

        if ($name = $criteria->getName()) {
            if (is_array($name)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in('status.name', ':name'));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('status.name', ':name'));
            }
            $queryBuilder->setParameter('name', $name);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
