<?php

declare(strict_types=1);

namespace Mush\Daedalus\Query;

use Doctrine\DBAL\Connection;
use Mush\Daedalus\ViewModel\RankingDaedalusViewModel;

final readonly class GetDaedalusRankingQueryHandler
{
    public function __construct(private Connection $connection) {}

    public function execute(GetDaedalusRankingQuery $query): array
    {
        return [
            'data' => array_map(static fn (array $row) => RankingDaedalusViewModel::fromQueryRow($row), $this->getRankingData($query)),
            'totalItems' => $this->getTotalCount($query),
        ];
    }

    private function getRankingData(GetDaedalusRankingQuery $query): array
    {
        $sql = 'SELECT
                daedalus_closed.id AS id,
                daedalus_closed.end_cause AS end_cause,
                GREATEST(EXTRACT(EPOCH FROM daedalus_closed.finished_at - daedalus_closed.created_at), 0)
                    / NULLIF(60.0 * config_daedalus.cycle_length, 0) AS cycles_survived,
                GREATEST(EXTRACT(EPOCH FROM daedalus_closed.finished_at - daedalus_closed.created_at), 0)
                    / NULLIF(60.0 * config_daedalus.cycle_length, 0)
                    / NULLIF(config_daedalus.cycle_per_game_day, 0) AS days_survived,
                CASE
                    WHEN daedalus_closed.human_triumph_sum >= 0 THEN daedalus_closed.human_triumph_sum
                    ELSE COALESCE((
                        SELECT SUM(triumph)
                        FROM closed_player
                        WHERE closed_player.closed_daedalus_id = daedalus_closed.id
                        AND closed_player.is_mush = FALSE
                    ), 0)
                END AS human_triumph_sum,
                CASE
                    WHEN daedalus_closed.mush_triumph_sum >= 0 THEN daedalus_closed.mush_triumph_sum
                    ELSE COALESCE((
                        SELECT SUM(triumph)
                        FROM closed_player
                        WHERE closed_player.closed_daedalus_id = daedalus_closed.id
                        AND closed_player.is_mush = TRUE
                    ), 0)
                END AS mush_triumph_sum,
                COALESCE((
                    SELECT MAX(triumph)
                    FROM closed_player
                    WHERE closed_player.closed_daedalus_id = daedalus_closed.id
                    AND closed_player.is_mush = FALSE
                ), 0) AS highest_human_triumph,
                COALESCE((
                    SELECT MAX(triumph)
                    FROM closed_player
                    WHERE closed_player.closed_daedalus_id = daedalus_closed.id
                    AND closed_player.is_mush = TRUE
                ), 0) AS highest_mush_triumph,
                config_localization.language AS language
            FROM daedalus_closed
            INNER JOIN daedalus_info
            ON daedalus_closed.daedalus_info_id = daedalus_info.id
            INNER JOIN config_game
            ON daedalus_info.game_config_id = config_game.id
            INNER JOIN config_daedalus
            ON config_game.daedalus_config_id = config_daedalus.id
            INNER JOIN config_localization
            ON daedalus_info.localization_config_id = config_localization.id
            WHERE daedalus_closed.finished_at IS NOT NULL
            AND daedalus_closed.created_at IS NOT NULL
            AND daedalus_closed.is_cheater = FALSE';

        $params = [
            'limit' => $query->itemsPerPage,
            'offset' => ($query->page - 1) * $query->itemsPerPage,
        ];

        if ($query->language !== '') {
            $sql .= '
            AND daedalus_info.localization_config_id IN (
                SELECT id FROM config_localization WHERE language = :language
            )';
            $params['language'] = $query->language;
        }

        $sql .= '
            ORDER BY cycles_survived DESC, daedalus_closed.id ASC
            LIMIT :limit OFFSET :offset';

        return $this->connection->executeQuery($sql, $params)->fetchAllAssociative();
    }

    private function getTotalCount(GetDaedalusRankingQuery $query): int
    {
        $countSql = 'SELECT COUNT(*) AS total
            FROM daedalus_closed
            INNER JOIN daedalus_info
            ON daedalus_closed.daedalus_info_id = daedalus_info.id
            INNER JOIN config_game
            ON daedalus_info.game_config_id = config_game.id
            INNER JOIN config_daedalus
            ON config_game.daedalus_config_id = config_daedalus.id
            INNER JOIN config_localization
            ON daedalus_info.localization_config_id = config_localization.id
            WHERE daedalus_closed.finished_at IS NOT NULL
            AND daedalus_closed.created_at IS NOT NULL
            AND daedalus_closed.is_cheater = FALSE';

        $countParams = [];

        if ($query->language !== '') {
            $countSql .= '
            AND daedalus_info.localization_config_id IN (
                SELECT id FROM config_localization WHERE language = :language
            )';
            $countParams['language'] = $query->language;
        }

        return (int) $this->connection->executeQuery($countSql, $countParams)->fetchOne();
    }
}
