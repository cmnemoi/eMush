<?php

namespace Mush\Status\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;

/**
 * @template-extends ServiceEntityRepository<Status>
 */
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
            ->leftJoin(Place::class, 'place', Join::WITH, 'place = status_target.place')
            ->leftJoin(GameEquipment::class, 'equipment', Join::WITH, 'equipment = status_target.gameEquipment')
            ->leftJoin(GameItem::class, 'item', Join::WITH, 'item = status_target.gameEquipment')
            ->leftJoin(Player::class, 'item_player', Join::WITH, 'item.player = item_player')
            ->leftJoin(Place::class, 'item_place', Join::WITH, 'item.place = item_place')
            ->leftJoin(Place::class, 'equipment_place', Join::WITH, 'equipment.place = equipment_place')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('player.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('place.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('item_player.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('item_place.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('equipment_place.daedalus', ':daedalus')
                )
            )
            ->setParameter('daedalus', $criteria->getDaedalus())
        ;

        if ($name = $criteria->getName()) {
            if (is_array($name)) {
                $queryBuilder
                    ->join(StatusConfig::class, 'status_config', Join::WITH, 'status_config = status.statusConfig')
                    ->andWhere($queryBuilder->expr()->in('status_config.statusName', ':name'));
            } else {
                $queryBuilder
                    ->join(StatusConfig::class, 'status_config', Join::WITH, 'status_config = status.statusConfig')
                    ->andWhere($queryBuilder->expr()->eq('status_config.statusName', ':name'));
            }
            $queryBuilder->setParameter('name', $name);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByTargetAndName(StatusHolderInterface $target, string $name): ?Status
    {
        $queryBuilder = $this->createQueryBuilder('status');

        $queryBuilder
            ->join(StatusConfig::class, 'status_config', Join::WITH, 'status_config = status.statusConfig')
            ->leftJoin(StatusTarget::class, 'status_target', Join::WITH, 'status_target = status.target')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('status_target.player', ':status_target'),
                $queryBuilder->expr()->eq('status_target.place', ':status_target'),
                $queryBuilder->expr()->eq('status_target.gameEquipment', ':status_target')
            ))
            ->andWhere($queryBuilder->expr()->eq('status_config.statusName', ':name'))
            ->setParameter(':status_target', $target)
            ->setParameter(':name', $name)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
