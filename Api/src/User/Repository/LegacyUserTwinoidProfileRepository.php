<?php

declare(strict_types=1);

namespace Mush\User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\User\Entity\LegacyUser;
use Mush\User\Entity\LegacyUserTwinoidProfile;

/**
 * @template-extends ServiceEntityRepository<LegacyUserTwinoidProfile>
 */
class LegacyUserTwinoidProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LegacyUserTwinoidProfile::class);
    }

    public function findOneByLegacyUser(LegacyUser $legacyUser): ?LegacyUserTwinoidProfile
    {
        $queryBuilder = $this->createQueryBuilder('legacy_user_twinoid_profile');

        $queryBuilder
            ->innerJoin('legacy_user_twinoid_profile.legacyUser', 'legacy_user')
            ->where($queryBuilder->expr()->eq('legacy_user.id', ':legacy_user_id'))
            ->setParameter('legacy_user_id', $legacyUser->getId())
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
