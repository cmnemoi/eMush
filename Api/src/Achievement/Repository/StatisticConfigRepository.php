<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;

/**
 * @extends ServiceEntityRepository<StatisticConfig>
 */
final class StatisticConfigRepository extends ServiceEntityRepository implements StatisticConfigRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatisticConfig::class);
    }

    public function findOneByName(StatisticEnum $name): StatisticConfig
    {
        $config = $this->findOneByNameOrNull($name);
        if ($config === null) {
            throw new \Exception("Statistic config {$name->value} not found!");
        }

        return $config;
    }

    public function findOneByNameOrNull(StatisticEnum $name): ?StatisticConfig
    {
        return $this->findOneBy(['name' => $name]);
    }
}
