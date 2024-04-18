<?php

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Enum\GameStatusEnum;
use Mush\User\Entity\User;
use Mush\User\Service\UserServiceInterface;

/**
 * @template-extends ServiceEntityRepository<DaedalusInfo>
 */
class DaedalusInfoRepository extends ServiceEntityRepository
{
    private UserServiceInterface $userService;

    public function __construct(ManagerRegistry $registry, UserServiceInterface $userService)
    {
        parent::__construct($registry, DaedalusInfo::class);
        $this->userService = $userService;
    }

    public function findAvailableDaedalus(string $name): ?DaedalusInfo
    {
        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':game_status'))
            ->andWhere($qb->expr()->eq('daedalus_info.name', ':name'))
            ->setMaxResults(1)
            ->setParameter('name', $name)
            ->setParameter('game_status', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAvailableDaedalusInLanguage(string $language): ?DaedalusInfo
    {
        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->leftJoin('daedalus_info.localizationConfig', 'language')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':game_status'))
            ->andWhere($qb->expr()->eq('language.name', ':language'))
            ->setMaxResults(1)
            ->setParameter('language', $language)
            ->setParameter('game_status', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY]);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAvailableDaedalusInLanguageForUser(string $language, User $user): ?DaedalusInfo
    {
        $userDaedaluses = $this->userService->findUserDaedaluses($user);

        if (\count($userDaedaluses) === 0) {
            return $this->findAvailableDaedalusInLanguage($language);
        }

        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->leftJoin('daedalus_info.localizationConfig', 'language')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':game_status'))
            ->andWhere($qb->expr()->eq('language.name', ':language'))
            ->andWhere($qb->expr()->notIn('daedalus_info', ':user_daedaluses'))
            ->setMaxResults(1)
            ->setParameter('language', $language)
            ->setParameter('game_status', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY])
            ->setParameter('user_daedaluses', $userDaedaluses);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function existAvailableDaedalus(): bool
    {
        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY]);

        return \count($qb->getQuery()->getResult()) > 0;
    }

    public function existAvailableDaedalusInLanguage(string $language): bool
    {
        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->leftJoin('daedalus_info.localizationConfig', 'language')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->andWhere($qb->expr()->eq('language.name', ':language'))
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY])
            ->setParameter('language', $language);

        return \count($qb->getQuery()->getResult()) > 0;
    }

    public function existAvailableDaedalusWithName(string $name): bool
    {
        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->andWhere($qb->expr()->eq('daedalus_info.name', ':name'))
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY])
            ->setParameter('name', $name);

        return \count($qb->getQuery()->getResult()) > 0;
    }
}
