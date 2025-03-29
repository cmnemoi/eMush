<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\TradeConfig;
use Mush\Communications\Enum\TradeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\Hunter;
use Mush\Place\Entity\Place;

/**
 * @extends ServiceEntityRepository<TradeConfig>
 */
final class TradeConfigRepository extends ServiceEntityRepository implements TradeConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradeConfig::class);
    }

    public function findOneByNameAndTransportIdOrThrow(TradeEnum $name, int $transportId): TradeConfig
    {
        $queryBuilder = $this->createQueryBuilder('trade_config')
            ->innerJoin(Hunter::class, 'hunter', Join::WITH, 'hunter.id = :transportId')
            ->innerJoin(Place::class, 'place', Join::WITH, 'place.id = hunter.space')
            ->innerJoin(Daedalus::class, 'daedalus', Join::WITH, 'daedalus.id = place.daedalus')
            ->where('trade_config.name = :name')
            ->setParameter('name', $name)
            ->setParameter('transportId', $transportId);

        $tradeConfig = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($tradeConfig === null) {
            throw new \RuntimeException("Trade config with name {$name->value} and transport id {$transportId} not found");
        }

        return $tradeConfig;
    }

    public function save(TradeConfig $tradeConfig): void
    {
        $this->getEntityManager()->persist($tradeConfig);
        $this->getEntityManager()->flush();
    }
}
