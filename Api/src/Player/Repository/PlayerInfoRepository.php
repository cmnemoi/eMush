<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

/**
 * @template-extends ServiceEntityRepository<PlayerInfo>
 */
final class PlayerInfoRepository extends ServiceEntityRepository implements PlayerInfoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerInfo::class);
    }

    public function getCurrentPlayerInfoForUserOrNull(User $user): ?PlayerInfo
    {
        $qb = $this->createQueryBuilder('player_info');

        $qb
            ->where($qb->expr()->eq('player_info.user', ':user'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('player_info.gameStatus', ':game_status_current'),
                $qb->expr()->eq('player_info.gameStatus', ':game_status_finished')
            ))
            ->setParameter('user', $user)
            ->setParameter('game_status_current', GameStatusEnum::CURRENT)
            ->setParameter('game_status_finished', GameStatusEnum::FINISHED);

        $playerInfo = $qb->getQuery()->getOneOrNullResult();

        return $playerInfo instanceof PlayerInfo ? $playerInfo : null;
    }

    public function findClosedGameByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('player_info');

        $qb
            ->where($qb->expr()->eq('player_info.user', ':user'))
            ->andWhere($qb->expr()->eq('player_info.gameStatus', ':game_status_closed'))
            ->setParameter('user', $user)
            ->setParameter('game_status_closed', GameStatusEnum::CLOSED);

        return $qb->getQuery()->getResult();
    }
}
