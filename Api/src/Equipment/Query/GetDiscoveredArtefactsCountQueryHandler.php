<?php

declare(strict_types=1);

namespace Mush\Equipment\Query;

use Doctrine\DBAL\Connection;
use Mush\Status\Enum\EquipmentStatusEnum;

final readonly class GetDiscoveredArtefactsCountQueryHandler
{
    public function __construct(private Connection $connection) {}

    public function execute(GetDiscoveredArtefactsCountQuery $query): int
    {
        $sql = 'SELECT COUNT(DISTINCT game_equipment.id)
            FROM game_equipment
            INNER JOIN room
            ON game_equipment.place_id = room.id
            INNER JOIN daedalus
            ON room.daedalus_id = daedalus.id
            INNER JOIN status_target
            ON status_target.game_equipment_id = game_equipment.id
            INNER JOIN status
            ON status.owner_id = status_target.id
            INNER JOIN status_config
            ON status.status_config_id = status_config.id
            WHERE daedalus.id = :daedalus
            AND status_config.status_name = :statusName;';

        $params = [
            'daedalus' => $query->daedalusId,
            'statusName' => EquipmentStatusEnum::ALIEN_ARTEFACT,
        ];

        return (int) $this->connection->executeQuery($sql, $params)->fetchOne();
    }
}
