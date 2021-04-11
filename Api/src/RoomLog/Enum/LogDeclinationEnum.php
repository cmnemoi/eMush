<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;

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
        ];
    }
}
