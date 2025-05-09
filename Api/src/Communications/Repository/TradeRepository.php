<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\Trade;
use Mush\Hunter\Entity\Hunter;

final class TradeRepository extends ServiceEntityRepository implements TradeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trade::class);
    }

    public function findAllByDaedalusId(int $daedalusId): array
    {
        $qb = $this->createQueryBuilder('trade')
            ->select('trade', 'hunter', 'room')
            ->innerJoin('trade.transport', 'hunter')
            ->innerJoin('hunter.space', 'room')
            ->where('room.daedalus = :daedalusId')
            ->setParameter('daedalusId', $daedalusId);

        $result = $qb->getQuery()->getResult();

        return array_map(fn (Trade $trade) => $this->hydrate($trade), $result);
    }

    public function findByTransportId(int $transportId): ?Trade
    {
        return $this->findOneBy(['transport' => $transportId]);
    }

    public function isThereAvailableTrade(int $daedalusId): bool
    {
        $query = <<<'SQL'
            SELECT EXISTS (
                SELECT 1
                FROM trade
                INNER JOIN hunter ON trade.transport_id = hunter.id
                INNER JOIN room ON hunter.space_id = room.id
                WHERE room.daedalus_id = :daedalusId
            )
        SQL;

        $connection = $this->getEntityManager()->getConnection();

        return $connection->executeQuery(
            $query,
            ['daedalusId' => $daedalusId]
        )->fetchOne();
    }

    public function save(Trade $trade): void
    {
        $entityManager = $this->getEntityManager();

        $trade->setTransport($entityManager->getReference(Hunter::class, $trade->getTransportId()));

        $entityManager->persist($trade);
        $entityManager->flush();
    }

    public function deleteByTradeOptionId(int $tradeOptionId): void
    {
        // First find the trade associated with the trade option
        $queryBuilder = $this->createQueryBuilder('trade')
            ->select('trade')
            ->innerJoin('trade.tradeOptions', 'tradeOption')
            ->where('tradeOption.id = :tradeOptionId')
            ->setParameter('tradeOptionId', $tradeOptionId);

        $trade = $queryBuilder->getQuery()->getSingleResult();

        // Delete the trade using the entity manager to properly handle cascade operations
        $this->getEntityManager()->remove($trade);
        $this->getEntityManager()->flush();
    }

    public function deleteByTransportId(int $transportId): void
    {
        // First find the trade associated with the transport
        $queryBuilder = $this->createQueryBuilder('trade')
            ->select('trade')
            ->where('trade.transport = :transportId')
            ->setParameter('transportId', $transportId);

        $trade = $queryBuilder->getQuery()->getSingleResult();

        // Delete the trade using the entity manager to properly handle cascade operations
        $this->getEntityManager()->remove($trade);
        $this->getEntityManager()->flush();
    }

    private function hydrate(?Trade $trade): ?Trade
    {
        if ($trade === null) {
            return null;
        }

        $trade->setTransportId($trade->getTransport()->getId());

        return $trade;
    }
}
