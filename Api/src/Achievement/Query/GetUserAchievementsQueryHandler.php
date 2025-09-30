<?php

declare(strict_types=1);

namespace Mush\Achievement\Query;

use Doctrine\DBAL\Connection;
use Mush\Achievement\ViewModel\AchievementViewModel;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetUserAchievementsQueryHandler
{
    public function __construct(private Connection $connection) {}

    /**
     * @return AchievementViewModel[]
     */
    public function __invoke(GetUserAchievementsQuery $query): array
    {
        $results = $this->connection->executeQuery(
            'SELECT
                achievement_config.name AS key,
                achievement_config.points,
                achievement_config.unlock_threshold as threshold,
                statistic_config.name AS statistic_key,
                statistic_config.is_rare
            FROM achievement
            INNER JOIN statistic ON statistic.id = achievement.statistic_id
            INNER JOIN statistic_config ON statistic_config.id = statistic.config_id
            INNER JOIN achievement_config ON achievement_config.id = achievement.config_id
            WHERE statistic.user_id = :userId
            ORDER BY achievement_config.name, statistic.count
            ',
            [
                'userId' => $query->userId,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row) => AchievementViewModel::fromQueryRow($row), $results);
    }
}
