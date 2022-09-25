<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Communication\Enum\NeronMessageEnum;

class LogDeclinationEnum
{
    public static function getVersionNumber(): array
    {
        return [
            ActionLogEnum::CONSUME_SUCCESS => ['version' => 46],
            ActionLogEnum::REPAIR_SUCCESS => ['version' => 7],
            ActionLogEnum::REPAIR_FAIL => ['versionPart1' => 10, 'versionPart2' => 50],
            ActionLogEnum::HIT_SUCCESS => ['version' => 5],
            ActionLogEnum::HIT_FAIL => ['version' => 2],
            ActionLogEnum::MOTIVATIONAL_SPEECH => ['version' => 19],
            ActionLogEnum::BORING_SPEECH => ['version' => 3],
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
            LogEnum::SELF_SURGERY_SUCCESS => ['version' => 2],
            LogEnum::SURGERY_SUCCESS => ['version' => 2],
            DiseaseMessagesEnum::REPLACE_COPROLALIA => [
                'version' => 13,
                'word' => 20,
                'animal' => 14,
                'prefix' => 4,
                'adjective' => 6,
                'balls' => 9,
            ],
            DiseaseMessagesEnum::PRE_COPROLALIA => [
                'version' => 3,
                'word' => 20,
                'animal' => 14,
                'prefix' => 4,
                'adjective' => 6,
                'balls' => 9,
            ],
            DiseaseMessagesEnum::POST_COPROLALIA => [
                'version' => 3,
                'word' => 20,
                'animal' => 14,
                'prefix' => 4,
                'adjective' => 6,
                'balls' => 9,
            ],
            DiseaseMessagesEnum::REPLACE_PARANOIA => ['version' => 12, 'paranoia_version4' => 4, 'paranoia_version6' => 6],
            DiseaseMessagesEnum::ACCUSE_PARANOIA => ['version' => 10, 'paranoia_version4' => 4, 'paranoia_version6' => 6],
        ];
    }
}
