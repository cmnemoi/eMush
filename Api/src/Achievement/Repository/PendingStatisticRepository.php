<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Achievement\Entity\PendingStatistic;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\User\Entity\User;

/**
 * @extends ServiceEntityRepository<PendingStatistic>
 */
final class PendingStatisticRepository extends ServiceEntityRepository implements PendingStatisticRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private StatisticConfigRepository $statisticConfigRepository,
    ) {
        parent::__construct($registry, PendingStatistic::class);
    }

    /**
     * @return PendingStatistic[]
     */
    public function findAllByClosedDaedalusId(int $closedDaedalusId): array
    {
        $queryBuilder = $this->createQueryBuilder('pending_statistic')
            ->innerJoin('pending_statistic.config', 'statisticConfig')
            ->where('pending_statistic.closedDaedalusId = :closedDaedalusId')
            ->setParameter('closedDaedalusId', $closedDaedalusId);

        return array_map(fn (PendingStatistic $statistic) => $this->hydrate($statistic), $queryBuilder->getQuery()->getResult());
    }

    public function findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum $name, int $userId, int $closedDaedalusId): ?PendingStatistic
    {
        $queryBuilder = $this->createQueryBuilder('pending_statistic')
            ->innerJoin('pending_statistic.config', 'statisticConfig')
            ->where('statisticConfig.name = :name')
            ->andWhere('pending_statistic.userId = :userId')
            ->andWhere('pending_statistic.closedDaedalusId = :closedDaedalusId')
            ->setParameter('name', $name)
            ->setParameter('userId', $userId)
            ->setParameter('closedDaedalusId', $closedDaedalusId);

        return $this->hydrate($queryBuilder->getQuery()->getOneOrNullResult());
    }

    public function findOrCreateByNameUserIdAndClosedDaedalusId(StatisticEnum $name, int $userId, int $closedDaedalusId): PendingStatistic
    {
        $pendingStatistic = $this->findByNameUserIdAndDaedalusIdOrNull($name, $userId, $closedDaedalusId);
        if (!$pendingStatistic) {
            $pendingStatistic = new PendingStatistic(
                config: $this->statisticConfigRepository->findOneByName($name),
                userId: $userId,
                closedDaedalusId: $closedDaedalusId
            );
        }

        return $pendingStatistic;
    }

    public function findOneById(int $id): PendingStatistic
    {
        $pendingStatistic = $this->findOneBy(['id' => $id]);

        return $this->hydrate($pendingStatistic);
    }

    public function save(PendingStatistic $pendingStatistic): void
    {
        $entityManager = $this->getEntityManager();

        $pendingStatistic->setUser($entityManager->getReference(User::class, $pendingStatistic->getUserId()));
        $pendingStatistic->setClosedDaedalus($entityManager->getReference(ClosedDaedalus::class, $pendingStatistic->getClosedDaedalusId()));

        $entityManager->persist($pendingStatistic);
        $entityManager->flush();
    }

    public function delete(PendingStatistic $pendingStatistic): void
    {
        $this->getEntityManager()->remove($pendingStatistic);
        $this->getEntityManager()->flush();
    }

    private function hydrate(?PendingStatistic $pendingStatistic): ?PendingStatistic
    {
        if ($pendingStatistic === null) {
            return null;
        }

        $pendingStatistic->setUserId($pendingStatistic->getUser()->getId());
        $pendingStatistic->setClosedDaedalusId($pendingStatistic->getClosedDaedalus()->getId());

        return $pendingStatistic;
    }
}
