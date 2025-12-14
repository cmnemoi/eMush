<?php

declare(strict_types=1);

namespace Mush\MetaGame\Query;

use Doctrine\DBAL\Connection;
use Mush\MetaGame\ViewModel\FillingDaedalusViewModel;

final readonly class GetFillingDaedalusesQueryHandler
{
    public function __construct(private Connection $connection) {}

    /** @return FillingDaedalusViewModel[] */
    public function execute(GetFillingDaedalusesQuery $query): array
    {
        $sql = "SELECT
                config_localization.language AS language,
                daedalus.day AS day,
                daedalus.cycle AS cycle,
                COUNT(player.id) AS current_players,
                config_daedalus.player_count AS max_players
            FROM daedalus_info
            INNER JOIN daedalus
            ON daedalus_info.daedalus_id = daedalus.id
            INNER JOIN config_localization
            ON daedalus_info.localization_config_id = config_localization.id
            INNER JOIN config_game
            ON daedalus_info.game_config_id = config_game.id
            INNER JOIN config_daedalus
            ON config_game.daedalus_config_id = config_daedalus.id
            LEFT JOIN player
            ON daedalus_info.daedalus_id = player.daedalus_id
            WHERE daedalus_info.game_status IN ('standby', 'starting')
            GROUP BY config_localization.language, config_daedalus.player_count, daedalus.day, daedalus.cycle
            ORDER BY config_localization.language ASC";

        $rows = $this->connection->executeQuery($sql)->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['language']] = FillingDaedalusViewModel::fromQueryRow($row);
        }

        return $result;
    }
}
