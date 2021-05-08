<?php

namespace Mush\Equipment\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class GameEquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameEquipment::class);
    }

    public function findByCriteria(GameEquipmentCriteria $criteria): array
    {
        $queryBuilder = $this->createQueryBuilder('equipment');

        $queryBuilder
            ->leftJoin(GameItem::class, 'item', Join::WITH, 'item = equipment')
            ->leftJoin(Player::class, 'item_player', Join::WITH, 'item.player = item_player')
            ->leftJoin(Place::class, 'item_place', Join::WITH, 'item.place = item_place')
            ->leftJoin(Place::class, 'equipment_place', Join::WITH, 'equipment.place = equipment_place')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('item_player.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('item_place.daedalus', ':daedalus'),
                    $queryBuilder->expr()->eq('equipment_place.daedalus', ':daedalus')
                )
            )
            ->setParameter('daedalus', $criteria->getDaedalus())
        ;

        if ($criteria->isBreakable() !== null) {
            $queryBuilder
                ->leftJoin(EquipmentConfig::class, 'equipment_config', Join::WITH, 'equipment.equipment = equipment_config')
                ->andWhere($queryBuilder->expr()->eq('equipment_config.isBreakable', ':isBreakable'))
                ->setParameter('isBreakable', $criteria->isBreakable())
                ;
        }

        if (($instanceOfs = $criteria->getInstanceOf()) !== null) {
            $types = [];
            foreach ($instanceOfs as $type) {
                $types[] = $queryBuilder->expr()->isInstanceOf('equipment', $type);
            }
            $queryBuilder->andWhere(
                call_user_func_array([$queryBuilder->expr(), 'orX'], $types)
            );
        }

        if (($notInstanceOfs = $criteria->getNotInstanceOf()) !== null) {
            $types = [];
            foreach ($notInstanceOfs as $type) {
                $types[] = $queryBuilder->expr()->not($queryBuilder->expr()->isInstanceOf('equipment', $type));
            }
            $queryBuilder->andWhere(
                call_user_func_array([$queryBuilder->expr(), 'andX'], $types)
            );
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
