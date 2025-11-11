<?php

declare(strict_types=1);

namespace Mush\MetaGame\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mush\MetaGame\Entity\Collection\ModerationSanctionCollection;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

/**
 * @template-extends ServiceEntityRepository<ModerationSanction>
 */
final class ModerationSanctionRepository extends ServiceEntityRepository implements ModerationSanctionRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModerationSanction::class);

        $this->entityManager = $this->getEntityManager();
    }

    /**
     * @return ModerationSanction[]
     */
    public function findAllUserActiveBansAndWarnings(User $user): array
    {
        $sql = <<<'EOD'
        SELECT *
        FROM moderationSanction
        WHERE user_id = :userId
        AND end_date > NOW()
        AND moderation_action IN ('ban_user', 'warning')
        EOD;

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(ModerationSanction::class, 'moderation_sanction');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }

    public function findUserAllActiveWarnings(User $user): ModerationSanctionCollection
    {
        $queryBuilder = $this->createQueryBuilder('moderation_sanction');

        $queryBuilder
            ->select('moderation_sanction')
            ->where('moderation_sanction.user = :userId')
            ->andWhere('moderation_sanction.moderationAction = :moderationAction')
            ->andWhere('moderation_sanction.startDate >= :now')
            ->setParameter('userId', $user->getId())
            ->setParameter('moderationAction', ModerationSanctionEnum::WARNING)
            ->setParameter('now', new \DateTime());

        return new ModerationSanctionCollection($queryBuilder->getQuery()->getArrayResult());
    }

    public function findUserActiveBan(User $user): ?ModerationSanction
    {
        $queryBuilder = $this->createQueryBuilder('moderation_sanction');

        $queryBuilder
            ->select('moderation_sanction')
            ->where('moderation_sanction.user = :userId')
            ->andWhere('moderation_sanction.moderationAction = :moderationAction')
            ->andWhere('moderation_sanction.endDate >= :now')
            ->setParameter('userId', $user->getId())
            ->setParameter('moderationAction', ModerationSanctionEnum::BAN_USER)
            ->setParameter('now', new \DateTime())
            ->orderBy('moderation_sanction.startDate', 'DESC')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findAllBansNotYetTriggeredForUser(User $user): ModerationSanctionCollection
    {
        $sql = <<<'EOD'
        SELECT *
        FROM moderationSanction
        WHERE user_id = :userId
        AND moderation_action IN ('ban_user_pending')
        EOD;

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(ModerationSanction::class, 'moderation_sanction');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('userId', $user->getId());

        return new ModerationSanctionCollection($query->getResult());
    }

    public function findAllBansNotYetTriggeredForAll(): ModerationSanctionCollection
    {
        $sql = <<<'EOD'
        SELECT *
        FROM moderationSanction
        WHERE moderation_action IN ('ban_user_pending')
        EOD;

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(ModerationSanction::class, 'moderation_sanction');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);

        return new ModerationSanctionCollection($query->getResult());
    }

    public function findAllPlayerReport(PlayerInfo $player): array
    {
        $sql = <<<'EOD'
        SELECT *
        FROM moderationSanction
        WHERE player_id = :playerId
        AND moderation_action IN ('report', 'report_abusive', 'report_processed')
        EOD;

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(ModerationSanction::class, 'moderation_sanction');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter('playerId', $player->getId());

        return $query->getResult();
    }

    public function save(ModerationSanction $moderationSanction): void
    {
        $this->entityManager->persist($moderationSanction);
        $this->entityManager->flush();
    }
}
