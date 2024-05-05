<?php

declare(strict_types=1);

namespace Mush\MetaGame\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
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
    public function findAllUserWarnings(User $user): array
    {
        $sql = 'SELECT moderationSanction.* FROM moderationSanction WHERE moderationSanction.user_id = :userId AND moderationSanction.moderation_action = :moderationAction';

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(ModerationSanction::class, 'moderation_sanction');

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query
            ->setParameter('userId', $user->getId())
            ->setParameter('moderationAction', ModerationSanctionEnum::WARNING);

        return $query->getResult();
    }
}
