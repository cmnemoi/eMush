<?php

declare(strict_types=1);

namespace Mush\MetaGame\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;

/**
 * @template-extends ServiceEntityRepository<ModerationSanction>
 */
final class ModerationSanctionRepository extends ServiceEntityRepository
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
}
