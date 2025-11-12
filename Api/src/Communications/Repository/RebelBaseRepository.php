<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @template-extends ServiceEntityRepository<RebelBase>
 */
final class RebelBaseRepository extends ServiceEntityRepository implements RebelBaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RebelBase::class);
    }

    public function areAllRebelBasesDecoded(int $daedalusId): bool
    {
        $queryBuilder = $this->createQueryBuilder('rebelBase')
            ->select('rebelBase')
            ->where('rebelBase.daedalus = :daedalusId')
            ->andWhere('rebelBase.signal < 100')
            ->setParameter('daedalusId', $daedalusId);

        return \count($queryBuilder->getQuery()->getResult()) === 0;
    }

    public function deleteAllByDaedalusId(int $daedalusId): void
    {
        $this->createQueryBuilder('rebelBase')
            ->delete()
            ->where('rebelBase.daedalus = :daedalusId')
            ->setParameter('daedalusId', $daedalusId)
            ->getQuery()
            ->execute();
    }

    /**
     * @return RebelBase[]
     */
    public function findAllByDaedalusId(int $daedalusId): array
    {
        $queryBuilder = $this->createQueryBuilder('rebelBase')
            ->select('rebelBase')
            ->innerJoin('rebelBase.daedalus', 'daedalus')
            ->innerJoin('rebelBase.rebelBaseConfig', 'rebelBaseConfig')
            ->where('rebelBase.daedalus = :daedalusId')
            ->setParameter('daedalusId', $daedalusId)
            ->orderBy('rebelBaseConfig.contactOrder', 'ASC');

        $rebelBases = $queryBuilder->getQuery()->getResult();

        return array_map(fn (RebelBase $rebelBase) => $this->hydrate($rebelBase), $rebelBases);
    }

    /**
     * @return RebelBase[]
     */
    public function findAllContactingRebelBases(int $daedalusId): array
    {
        $queryBuilder = $this->createQueryBuilder('rebelBase')
            ->select('rebelBase')
            ->innerJoin('rebelBase.daedalus', 'daedalus')
            ->where('rebelBase.daedalus = :daedalusId')
            ->andWhere('rebelBase.contactStartDate IS NOT NULL')
            ->andWhere('rebelBase.contactEndDate IS NULL')
            ->setParameter('daedalusId', $daedalusId);

        return array_map(fn (RebelBase $rebelBase) => $this->hydrate($rebelBase), $queryBuilder->getQuery()->getResult());
    }

    public function findAllDecodedRebelBases(int $daedalusId): array
    {
        $queryBuilder = $this->createQueryBuilder('rebelBase')
            ->select('rebelBase')
            ->innerJoin('rebelBase.daedalus', 'daedalus')
            ->where('rebelBase.daedalus = :daedalusId')
            ->andWhere('rebelBase.signal >= 100')
            ->setParameter('daedalusId', $daedalusId);

        return array_map(fn (RebelBase $rebelBase) => $this->hydrate($rebelBase), $queryBuilder->getQuery()->getResult());
    }

    public function findByDaedalusIdAndNameOrThrow(int $daedalusId, RebelBaseEnum $name): RebelBase
    {
        $queryBuilder = $this->createQueryBuilder('rebelBase')
            ->select('rebelBase')
            ->innerJoin('rebelBase.rebelBaseConfig', 'rebelBaseConfig')
            ->innerJoin('rebelBase.daedalus', 'daedalus')
            ->where('rebelBase.daedalus = :daedalusId')
            ->andWhere('rebelBaseConfig.name = :name')
            ->setParameter('daedalusId', $daedalusId)
            ->setParameter('name', $name->toString());

        $rebelBase = $this->hydrate($queryBuilder->getQuery()->getOneOrNullResult());
        if ($rebelBase === null) {
            throw new \RuntimeException("Rebel base {$name->toString()} not found for daedalus {$daedalusId}");
        }

        return $rebelBase;
    }

    public function findMostRecentContactingRebelBase(int $daedalusId): ?RebelBase
    {
        $queryBuilder = $this->createQueryBuilder('rebelBase')
            ->select('rebelBase')
            ->innerJoin('rebelBase.rebelBaseConfig', 'rebelBaseConfig')
            ->innerJoin('rebelBase.daedalus', 'daedalus')
            ->where('rebelBase.daedalus = :daedalusId')
            ->andWhere('rebelBase.contactStartDate IS NOT NULL')
            ->setParameter('daedalusId', $daedalusId)
            ->orderBy('rebelBase.contactStartDate', 'DESC')
            ->setMaxResults(1);

        return $this->hydrate($queryBuilder->getQuery()->getOneOrNullResult());
    }

    public function findNextContactingRebelBase(int $daedalusId): ?RebelBase
    {
        $queryBuilder = $this->createQueryBuilder('rebelBase')
            ->select('rebelBase')
            ->innerJoin('rebelBase.rebelBaseConfig', 'rebelBaseConfig')
            ->innerJoin('rebelBase.daedalus', 'daedalus')
            ->where('rebelBase.daedalus = :daedalusId')
            ->andWhere('rebelBase.contactStartDate IS NULL')
            ->andWhere('rebelBase.contactEndDate IS NULL')
            ->setParameter('daedalusId', $daedalusId)
            ->orderBy('rebelBaseConfig.contactOrder', 'ASC')
            ->setMaxResults(1);

        return $this->hydrate($queryBuilder->getQuery()->getOneOrNullResult());
    }

    public function hasNoContactingRebelBase(int $daedalusId): bool
    {
        $connection = $this->getEntityManager()->getConnection();

        $query = <<<'EOD'
        SELECT EXISTS (
            SELECT 1
            FROM rebel_base
            WHERE daedalus_id = :daedalusId
            AND contact_start_date IS NOT NULL
            AND contact_end_date IS NULL
        ) as has_contacting_base
        EOD;

        $result = $connection->executeQuery(
            $query,
            ['daedalusId' => $daedalusId]
        )->fetchOne();

        return !$result;
    }

    public function save(RebelBase $rebelBase): void
    {
        $entityManager = $this->getEntityManager();

        $rebelBase->setDaedalus($entityManager->getReference(Daedalus::class, $rebelBase->getDaedalusId()));

        $entityManager->persist($rebelBase);
        $entityManager->flush();
    }

    private function hydrate(?RebelBase $rebelBase): ?RebelBase
    {
        if ($rebelBase === null) {
            return null;
        }

        $rebelBase->setDaedalusId($rebelBase->getDaedalus()->getId());

        return $rebelBase;
    }
}
