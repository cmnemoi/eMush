<?php

namespace Mush\User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template-extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername(string $username): ?User
    {
        $user = $this->findOneBy(['userId' => $username]);

        return $user instanceof User ? $user : null;
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $user = $this->findOneBy(['userId' => $identifier]);

        return $user instanceof User ? $user : null;
    }

    public function findUserDaedaluses(User $user): array
    {
        $qb = $this->createQueryBuilder('user');

        $qb
            ->select('daedalus_info')
            ->leftJoin(PlayerInfo::class, 'player_info', 'WITH', 'player_info.user = user.id')
            ->leftJoin('player_info.player', 'player')
            ->leftJoin('player.daedalus', 'daedalus')
            ->leftJoin(DaedalusInfo::class, 'daedalus_info', 'WITH', 'daedalus_info.id = daedalus.id')
            ->where($qb->expr()->eq('user.id', ':user_id'))
            ->setParameter('user_id', $user->getId())
        ;

        return $qb->getQuery()->getResult();
    }
}
