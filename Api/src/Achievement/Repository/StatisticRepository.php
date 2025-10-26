<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\User\Entity\User;

/**
 * @extends ServiceEntityRepository<Statistic>
 */
final class StatisticRepository extends ServiceEntityRepository implements StatisticRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private StatisticConfigRepository $statisticConfigRepository,
    ) {
        parent::__construct($registry, Statistic::class);
    }

    public function findByNameAndUserIdOrNull(StatisticEnum $name, int $userId): ?Statistic
    {
        $queryBuilder = $this->createQueryBuilder('statistic')
            ->innerJoin('statistic.config', 'statisticConfig')
            ->where('statisticConfig.name = :name')
            ->andWhere('statistic.userId = :userId')
            ->setParameter('name', $name)
            ->setParameter('userId', $userId);

        return $this->hydrate($queryBuilder->getQuery()->getOneOrNullResult());
    }

    public function findOrCreateByNameAndUserId(StatisticEnum $name, int $userId): Statistic
    {
        $statistic = $this->findByNameAndUserIdOrNull($name, $userId);
        if (!$statistic) {
            $statistic = new Statistic(
                config: $this->statisticConfigRepository->findOneByName($name),
                userId: $userId,
            );
        }

        return $statistic;
    }

    public function findOneById(int $id): Statistic
    {
        $statistic = $this->findOneBy(['id' => $id]);

        return $this->hydrate($statistic);
    }

    public function save(Statistic $statistic): void
    {
        $entityManager = $this->getEntityManager();

        $statistic->setUser($entityManager->getReference(User::class, $statistic->getUserId()));

        $entityManager->persist($statistic);
        $entityManager->flush();
    }

    private function hydrate(?Statistic $statistic): ?Statistic
    {
        if ($statistic === null) {
            return null;
        }

        $statistic->setUserId($statistic->getUser()->getId());

        return $statistic;
    }
}
