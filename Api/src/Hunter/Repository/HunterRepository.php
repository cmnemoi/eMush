<?php

declare(strict_types=1);

namespace Mush\Hunter\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\Trade;
use Mush\Communications\Entity\TradeOption;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;

/**
 * @template-extends ServiceEntityRepository<Hunter>
 */
final class HunterRepository extends ServiceEntityRepository implements HunterRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hunter::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function delete(Hunter $hunter): void
    {
        $space = $hunter->getSpace();
        $space->removeHunter($hunter);

        $this->entityManager->remove($hunter);
        $this->entityManager->flush($hunter);
    }

    public function findByIdOrThrow(int $id): Hunter
    {
        $hunter = $this->find($id);

        if ($hunter === null) {
            throw new \RuntimeException("Hunter not found for id {$id}");
        }

        return $hunter;
    }

    public function findOneByTargetOrThrow(HunterTarget $hunterTarget): Hunter
    {
        $hunter = $this->findOneBy(['target' => $hunterTarget]);

        if ($hunter === null) {
            throw new \RuntimeException("Hunter not found for target {$hunterTarget->getId()}");
        }

        return $hunter;
    }

    public function findByTradeOptionIdOrThrow(int $tradeOptionId): Hunter
    {
        $queryBuilder = $this->createQueryBuilder('hunter');
        $queryBuilder
            ->innerJoin(Trade::class, 'trade', Join::WITH, 'trade.transport = hunter')
            ->innerJoin(TradeOption::class, 'tradeOption', Join::WITH, 'tradeOption.trade = trade')
            ->where('tradeOption.id = :tradeOptionId')
            ->setParameter('tradeOptionId', $tradeOptionId);

        $hunter = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!$hunter) {
            throw new \RuntimeException("Hunter not found for trade option {$tradeOptionId}");
        }

        return $hunter;
    }

    public function findByTradeIdOrThrow(int $tradeId): Hunter
    {
        $queryBuilder = $this->createQueryBuilder('hunter');
        $queryBuilder
            ->innerJoin(Trade::class, 'trade', Join::WITH, 'trade.transport = hunter')
            ->where('trade.id = :tradeId')
            ->setParameter('tradeId', $tradeId);

        $hunter = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!$hunter) {
            throw new \RuntimeException("Hunter not found for trade {$tradeId}");
        }

        return $hunter;
    }

    public function save(Hunter $hunter): void
    {
        $this->entityManager->persist($hunter);
        $this->entityManager->flush();
    }
}
