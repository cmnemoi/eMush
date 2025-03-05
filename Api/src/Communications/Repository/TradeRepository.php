<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Trade::class, 't');
        $rsm->addJoinedEntityFromClassMetadata(Hunter::class, 'h', 't', 'transport', ['id' => 'transport_id']);

        $sql = <<<'SQL'
            SELECT trade.*, hunter.*
            FROM trade
            INNER JOIN hunter ON trade.transport_id = hunter.id
            INNER JOIN room ON hunter.space_id = room.id
            WHERE room.daedalus_id = :daedalusId
        SQL;

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('daedalusId', $daedalusId);

        $result = $query->getResult();

        return array_map(fn (Trade $trade) => $this->hydrate($trade), $result);
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

    private function hydrate(?Trade $trade): ?Trade
    {
        if ($trade === null) {
            return null;
        }

        $trade->setTransportId($trade->getTransport()->getId());

        return $trade;
    }
}
