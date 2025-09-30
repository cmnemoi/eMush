<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Achievement\Entity\Achievement;
use Mush\Achievement\Entity\Statistic;

/**
 * @extends ServiceEntityRepository<Achievement>
 */
final class AchievementRepository extends ServiceEntityRepository implements AchievementRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achievement::class);
    }

    public function existsForStatistic(int $statisticId): bool
    {
        return $this->findOneBy(['statistic' => $statisticId]) !== null;
    }

    public function save(Achievement $achievement): void
    {
        $entityManager = $this->getEntityManager();

        $achievement->setStatistic($entityManager->getReference(Statistic::class, $achievement->getStatisticId()));

        $entityManager->persist($achievement);
        $entityManager->flush();
    }

    public function findAllByStatistic(Statistic $statistic): array
    {
        return $this->createQueryBuilder('achievement')
            ->innerJoin('achievement.statistic', 'statistic')
            ->where('statistic.id = :statistic')
            ->setParameter('statistic', $statistic->getId())
            ->getQuery()
            ->getResult();
    }
}
