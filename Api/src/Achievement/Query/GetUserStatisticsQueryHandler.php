<?php

declare(strict_types=1);

namespace Mush\Achievement\Query;

use Doctrine\DBAL\Connection;
use Mush\Achievement\ViewModel\StatisticViewModel;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetUserStatisticsQueryHandler
{
    public function __construct(private Connection $connection) {}

    /**
     * @return StatisticViewModel[]
     */
    public function __invoke(GetUserStatisticsQuery $query): array
    {
        $results = $this->connection->executeQuery(
            '
            SELECT statistic_config.name, statistic.count, statistic_config.is_rare
            FROM statistic
            INNER JOIN statistic_config
            ON statistic.config_id = statistic_config.id
            WHERE user_id = :userId
            ORDER BY statistic_config.is_rare DESC, statistic.count DESC
            ',
            [
                'userId' => $query->userId,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row) => StatisticViewModel::fromQueryRow($row), $results);
    }
}
