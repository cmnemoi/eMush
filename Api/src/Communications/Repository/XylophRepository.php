<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\XylophEntry;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @template-extends ServiceEntityRepository<XylophEntry>
 */
final class XylophRepository extends ServiceEntityRepository implements XylophRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XylophEntry::class);
    }

    public function deleteAllByDaedalusId(int $daedalusId): void
    {
        $this->createQueryBuilder('xyloph_entry')
            ->delete()
            ->where('xyloph_entry.daedalus = :daedalusId')
            ->setParameter('daedalusId', $daedalusId)
            ->getQuery()
            ->execute();
    }

    /**
     * @return XylophEntry[]
     */
    public function findAllByDaedalusId(int $daedalusId): array
    {
        return array_map(fn (XylophEntry $xylophEntry) => $this->hydrate($xylophEntry), $this->findBy(['daedalus' => $daedalusId]));
    }

    /**
     * @return XylophEntry[]
     */
    public function findAllUndecodedByDaedalusId(int $daedalusId): array
    {
        return array_map(
            fn (XylophEntry $xylophEntry) => $this->hydrate($xylophEntry),
            $this->findBy(['daedalus' => $daedalusId, 'isDecoded' => false])
        );
    }

    public function findByDaedalusIdAndNameOrThrow(int $daedalusId, string $name): XylophEntry
    {
        $queryBuilder = $this->createQueryBuilder('xyloph_entry')
            ->select('xyloph_entry')
            ->innerJoin('xyloph_entry.xylophConfig', 'xylophConfig')
            ->innerJoin('xyloph_entry.daedalus', 'daedalus')
            ->where('xyloph_entry.daedalus = :daedalusId')
            ->andWhere('xylophConfig.name = :name')
            ->setParameter('daedalusId', $daedalusId)
            ->setParameter('name', $name);

        $xylophEntry = $this->hydrate($queryBuilder->getQuery()->getOneOrNullResult());
        if ($xylophEntry === null) {
            throw new \RuntimeException("XylophEntry {$name} not found for daedalus {$daedalusId}");
        }

        return $xylophEntry;
    }

    public function areAllXylophDatabasesDecoded(int $daedalusId): bool
    {
        $connection = $this->getEntityManager()->getConnection();

        $query = <<<'EOD'
        SELECT EXISTS (
            SELECT 1
            FROM xyloph_entry
            WHERE daedalus_id = :daedalusId
            AND is_decoded = false
        )
        EOD;

        $result = $connection->executeQuery(
            $query,
            [
                'daedalusId' => $daedalusId,
            ],
        )->fetchOne();

        return !$result;
    }

    public function save(XylophEntry $xylophEntry): void
    {
        $entityManager = $this->getEntityManager();

        $xylophEntry->setDaedalus($entityManager->getReference(Daedalus::class, $xylophEntry->getDaedalusId()));

        $entityManager->persist($xylophEntry);
        $entityManager->flush();
    }

    private function hydrate(?XylophEntry $xylophEntry): ?XylophEntry
    {
        if ($xylophEntry === null) {
            return null;
        }

        $xylophEntry->setDaedalusId($xylophEntry->getDaedalus()->getId());

        return $xylophEntry;
    }
}
