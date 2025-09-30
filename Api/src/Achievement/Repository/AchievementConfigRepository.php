<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Achievement\Entity\AchievementConfig;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\AchievementEnum;

/**
 * @extends ServiceEntityRepository<AchievementConfig>
 */
final class AchievementConfigRepository extends ServiceEntityRepository implements AchievementConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AchievementConfig::class);
    }

    public function findAllToUnlockForStatistic(Statistic $statistic): array
    {
        $queryBuilder = $this->createQueryBuilder('achievementConfig')
            ->innerJoin('achievementConfig.statisticConfig', 'statisticConfig')
            ->where('statisticConfig.id = :statisticConfig')
            ->andWhere('achievementConfig.unlockThreshold <= :statisticCount')
            ->setParameter('statisticConfig', $statistic->getConfig())
            ->setParameter('statisticCount', $statistic->getCount());

        return $queryBuilder->getQuery()->getResult();
    }

    public function findOneByNameOrNull(AchievementEnum $name): ?AchievementConfig
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(AchievementConfig $achievementConfig): void
    {
        $this->getEntityManager()->persist($achievementConfig);
        $this->getEntityManager()->flush();
    }
}
