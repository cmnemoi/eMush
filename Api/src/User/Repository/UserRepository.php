<?php

namespace Mush\User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @template-extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByIdOrThrow(int $id): User
    {
        $user = $this->findOneBy(['id' => $id]);
        if (!$user instanceof User) {
            throw new \RuntimeException("User with id {$id} not found");
        }

        return $user;
    }

    /**
     * @return User[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    public function loadUserByUsername(string $username): ?User
    {
        $user = $this->findOneBy(['username' => $username]);

        return $user instanceof User ? $user : null;
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        $user = $this->findOneBy(['userId' => $identifier]);

        return $user instanceof User ? $user : null;
    }

    public function findUserDaedaluses(User $user): array
    {
        $qb = $this->createQueryBuilder('user');

        $qb
            ->select('daedalus_info')
            ->innerJoin(PlayerInfo::class, 'player_info', 'WITH', 'player_info.user = user.id')
            ->innerJoin('player_info.player', 'player')
            ->innerJoin('player.daedalus', 'daedalus')

            ->innerJoin(DaedalusInfo::class, 'daedalus_info', 'WITH', 'daedalus_info.daedalus = daedalus.id')
            ->where($qb->expr()->eq('user.id', ':user_id'))
            ->setParameter('user_id', $user->getId());

        return $qb->getQuery()->getResult();
    }

    public function findUserClosedPlayers(User $user): array
    {
        $qb = $this->createQueryBuilder('user');

        $qb = $qb->select('closed_player')
            ->innerJoin(PlayerInfo::class, 'player_info', 'WITH', 'player_info.user = user.id')
            ->innerJoin(ClosedPlayer::class, 'closed_player', 'WITH', 'closed_player.id = player_info.closedPlayer')
            ->where('user.id = :user_id')
            ->setParameter('user_id', $user->getId());

        return $qb->getQuery()->getResult();
    }

    public function findUserPastCyclesCount(User $user): int
    {
        $query = <<<'SQL'
            SELECT
                SUM(
                    EXTRACT(
                        EPOCH FROM closed_player.finished_at - closed_player.created_at
                    ) / 60 / config_daedalus.cycle_length
                )
            as nb_cycles_survived
            FROM closed_player
            INNER JOIN player_info
            ON closed_player.id = player_info.closed_player_id
            INNER JOIN daedalus_closed AS closed_daedalus
            ON closed_player.closed_daedalus_id = closed_daedalus.id
            INNER JOIN daedalus_info
            ON closed_daedalus.daedalus_info_id = daedalus_info.id
            INNER JOIN config_game
            ON daedalus_info.game_config_id = config_game.id
            INNER JOIN config_daedalus
            ON config_game.daedalus_config_id = config_daedalus.id
            INNER JOIN users
            ON player_info.user_id = users.id
            WHERE users.id = :user_id
            AND closed_player.finished_at IS NOT NULL;
        SQL;

        $connection = $this->getEntityManager()->getConnection();

        return (int) $connection->executeQuery($query, ['user_id' => $user->getId()])->fetchOne() ?? 0;
    }

    public function findUserNumberOfPastGames(User $user): int
    {
        $qb = $this->createQueryBuilder('user');

        $qb->select('COUNT(closed_player)')
            ->innerJoin(PlayerInfo::class, 'player_info', 'WITH', 'player_info.user = user.id')
            ->innerJoin(ClosedPlayer::class, 'closed_player', 'WITH', 'closed_player.id = player_info.closedPlayer')
            ->where('user.id = :user_id')
            ->setParameter('user_id', $user->getId());

        return (int) $qb->getQuery()->getSingleScalarResult() ?? 0;
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
