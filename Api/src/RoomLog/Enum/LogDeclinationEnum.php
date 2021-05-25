<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Enum\NeronMessageEnum;

class LogDeclinationEnum
{
    public static function getVersionNumber(): array
    {
        return [
            ActionLogEnum::CONSUME_SUCCESS => ['version' => 46],
            ActionLogEnum::REPAIR_SUCCESS => ['version' => 7],
            ActionLogEnum::REPAIR_FAIL => ['versionPart1' => 10, 'versionPart2' => 50],
            ActionEnum::SHRED => ['version' => 4],
            ActionEnum::RETRIEVE_OXYGEN => ['version' => 10],
            NeronMessageEnum::ASPHYXIA_DEATH => ['versionPart1' => 3, 'versionPart2' => 9],
            NeronMessageEnum::BROKEN_EQUIPMENT => ['version' => 5],
            NeronMessageEnum::HUNTER_ARRIVAL => ['versionPart1' => 4, 'versionPart2' => 5],
            NeronMessageEnum::NEW_FIRE => ['versionPart1' => 3, 'versionPart2' => 3],
            NeronMessageEnum::NEW_PROJECT => ['versionPart2' => 7, 'versionPart1Unhinib' => 7, 'versionPart1Crazy' => 12],
            NeronMessageEnum::PLAYER_DEATH => ['version' => 7],
            NeronMessageEnum::REBEL_SIGNAL => ['version' => 5],
            NeronMessageEnum::REPORT_FIRE => ['version' => 5],
            NeronMessageEnum::TITLE_ATTRIBUTION => ['version' => 8],
            NeronMessageEnum::TRAVEL_ARRIVAL => ['version' => 11],
            NeronMessageEnum::SHIELD_BREACH => ['version' => 4],
            NeronMessageEnum::PATCHING_UP => ['versionPart1' => 10, 'versionPart2' => 4],
        ];
    }
}
