<?php

declare(strict_types=1);

namespace Mush\Player\Query;

use Doctrine\DBAL\Connection;
use Mush\Player\ViewModel\UserShipsHistoryViewModel;

final readonly class UserShipsHistoryQueryHandler
{
    public function __construct(private Connection $connection) {}

    public function execute(UserShipsHistoryQuery $query): array
    {
        $results = $this->getShipsHistoryData($query);

        return [
            'data' => array_map(static fn (array $row) => UserShipsHistoryViewModel::fromQueryRow($row), $results),
            'totalItems' => $this->getTotalCount($query),
        ];
    }

    private function getShipsHistoryData(UserShipsHistoryQuery $query): array
    {
        $sql
        = 'SELECT
            character_config.character_name AS character_name,
            GREATEST(EXTRACT(EPOCH FROM closed_player.finished_at - closed_player.created_at), 0)
                    / NULLIF(60.0 * config_daedalus.cycle_length, 0)
                    / NULLIF(config_daedalus.cycle_per_game_day, 0) AS days_survived,
            daedalus_info.daedalus_statistics_explorations_started AS nb_explorations,
            CASE
            WHEN
                daedalus_info.daedalus_projects_statistics_neron_projets_completed LIKE \'a:%:{%\' THEN
                split_part(daedalus_info.daedalus_projects_statistics_neron_projets_completed, \':\', 2)::integer
            ELSE 0 END AS nb_neron_projects,
            CASE
            WHEN
                daedalus_info.daedalus_projects_statistics_research_projets_completed LIKE \'a:%:{%\' THEN
                split_part(daedalus_info.daedalus_projects_statistics_research_projets_completed, \':\', 2)::integer
            ELSE 0 END AS nb_research_projects,
            daedalus_info.daedalus_statistics_planets_found AS nb_scanned_planets,
            player_info.titles AS titles,
            closed_player.triumph AS triumph,
            closed_player.end_cause AS end_cause,
            daedalus_closed.id AS daedalus_id,
            closed_player.is_mush AS was_mush
        FROM users
        INNER JOIN player_info
        ON users.id = player_info.user_id
        INNER JOIN closed_player
        ON player_info.closed_player_id = closed_player.id
        INNER JOIN daedalus_closed
        ON closed_player.closed_daedalus_id = daedalus_closed.id
        INNER JOIN daedalus_info
        ON daedalus_closed.id = daedalus_info.closed_daedalus_id
        INNER JOIN character_config
        ON player_info.character_config_id = character_config.id
        INNER JOIN config_game
        ON daedalus_info.game_config_id = config_game.id
        INNER JOIN config_daedalus
        ON config_game.daedalus_config_id = config_daedalus.id
        WHERE users.user_id = :user_id
        AND daedalus_closed.finished_at IS NOT NULL
        ORDER BY closed_player.finished_at DESC
        LIMIT :items_per_page
        OFFSET :offset';

        $params = [
            'user_id' => $query->userId,
            'items_per_page' => $query->itemsPerPage,
            'offset' => ($query->page - 1) * $query->itemsPerPage,
        ];

        return $this->connection->executeQuery($sql, $params)->fetchAllAssociative();
    }

    private function getTotalCount(UserShipsHistoryQuery $query): int
    {
        $countSql = 'SELECT COUNT(*) AS total
        FROM users
        INNER JOIN player_info
        ON users.id = player_info.user_id
        INNER JOIN closed_player
        ON player_info.closed_player_id = closed_player.id
        INNER JOIN daedalus_closed
        ON closed_player.closed_daedalus_id = daedalus_closed.id
        WHERE users.user_id = :user_id
        AND daedalus_closed.is_cheater = FALSE';

        $countParams = [
            'user_id' => $query->userId,
        ];

        return (int) $this->connection->executeQuery($countSql, $countParams)->fetchOne();
    }
}
