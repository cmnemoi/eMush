<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\User\Entity\LegacyUser;
use Mush\User\Entity\User;

/**
 * @template-extends ServiceEntityRepository<LegacyUser>
 */
class LegacyUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LegacyUser::class);
    }

    public function findOneByUser(User $user): ?LegacyUser
    {
        $queryBuilder = $this->createQueryBuilder('legacy_user');

        $queryBuilder
            ->innerJoin('legacy_user.user', 'user')
            ->where($queryBuilder->expr()->eq('user.userId', ':user_id'))
            ->setParameter('user_id', $user->getUserId())
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
