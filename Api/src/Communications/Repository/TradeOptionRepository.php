<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communications\Entity\TradeOption;

final class TradeOptionRepository extends ServiceEntityRepository implements TradeOptionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradeOption::class);
    }

    public function findByIdOrThrow(int $id): TradeOption
    {
        $tradeOption = $this->find($id);

        if (!$tradeOption) {
            throw new \RuntimeException("Trade option {$id} not found!");
        }

        return $tradeOption;
    }
}
