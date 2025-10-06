<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

/**
 * @template-extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository implements PlayerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function save(Player $player): void
    {
        $this->_em->persist($player);
        $this->_em->flush();
    }

    public function findOneByNameAndDaedalus(string $name, Daedalus $daedalus): ?Player
    {
        $qb = $this->createQueryBuilder('player');

        $qb
            ->leftJoin(PlayerInfo::class, 'player_info', Join::WITH, 'player.playerInfo = player_info')
            ->leftJoin(CharacterConfig::class, 'character_config', Join::WITH, 'playerInfo.characterConfig = character_config')
            ->where($qb->expr()->eq('character_config.name', ':name'))
            ->andWhere($qb->expr()->eq('player.daedalus', ':daedalus'))
            ->setParameter('name', $name)
            ->setParameter('daedalus', $daedalus);

        $player = $qb->getQuery()->getOneOrNullResult();

        return $player instanceof Player ? $player : null;
    }

    public function startTransaction(): void
    {
        $this->getEntityManager()->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $this->getEntityManager()->commit();
    }

    public function rollbackTransaction(): void
    {
        $this->getEntityManager()->rollback();
        $this->getEntityManager()->close();
    }

    public function delete(Player $player): void
    {
        $this->getEntityManager()->remove($player);
        $this->getEntityManager()->flush();
    }

    public function lockAndRefresh(Player $player, int $mode): void
    {
        $this->getEntityManager()->lock($player, $mode);
        $this->getEntityManager()->refresh($player);
    }

    public function getAll(): array
    {
        return $this->findAll();
    }

    public function findById(int $id): ?Player
    {
        return $this->find($id);
    }

    public function findOneByUserAndDaedalus(User $user, Daedalus $daedalus): ?Player
    {
        $qb = $this->createQueryBuilder('player');

        $qb
            ->innerJoin('player.playerInfo', 'playerInfo')
            ->where($qb->expr()->eq('playerInfo.user', ':user'))
            ->andWhere($qb->expr()->eq('player.daedalus', ':daedalus'))
            ->setParameter('user', $user)
            ->setParameter('daedalus', $daedalus)
            ->setMaxResults(1);

        $player = $qb->getQuery()->getOneOrNullResult();

        return $player instanceof Player ? $player : null;
    }

    public function findByUser(User $user): ?Player
    {
        $queryBuilder = $this->createQueryBuilder('player');

        $queryBuilder
            ->innerJoin('player.playerInfo', 'playerInfo')
            ->where($queryBuilder->expr()->eq('playerInfo.user', ':user'))
            ->setParameter('user', $user)
            ->setMaxResults(1);

        $player = $queryBuilder->getQuery()->getOneOrNullResult();

        return $player instanceof Player ? $player : null;
    }
}
