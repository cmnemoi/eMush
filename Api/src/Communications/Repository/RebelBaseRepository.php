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

    public function hasNoContactingRebelBase(int $daedalusId): bool
    {
        $connection = $this->getEntityManager()->getConnection();

        $query = <<<'EOD'
        SELECT EXISTS (
            SELECT 1
            FROM rebel_base
            WHERE daedalus_id = :daedalusId
            AND is_contacting = true
        ) as has_contacting_base
        EOD;

        $result = $connection->executeQuery(
            $query,
            ['daedalusId' => $daedalusId]
        )->fetchOne();

        return !$result;
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
