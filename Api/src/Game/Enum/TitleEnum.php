<?php

namespace Mush\Game\Enum;

use Mush\Status\Enum\PlayerStatusEnum;

abstract class TitleEnum
{
    public const string COMMANDER = 'commander';
    public const string NERON_MANAGER = 'neron_manager';
    public const string COM_MANAGER = 'com_manager';

    public const array TITLES_MAP = [
        self::COMMANDER => self::COMMANDER,
        self::NERON_MANAGER => self::NERON_MANAGER,
        self::COM_MANAGER => self::COM_MANAGER,
    ];

    public static function getHasGainedTitleStatusName(string $title): string
    {
        return match ($title) {
            self::COMMANDER => PlayerStatusEnum::HAS_GAINED_COMMANDER_TITLE,
            self::NERON_MANAGER => PlayerStatusEnum::HAS_GAINED_NERON_MANAGER_TITLE,
            self::COM_MANAGER => PlayerStatusEnum::HAS_GAINED_COM_MANAGER_TITLE,
        };
    }

    public static function isValidTitle(string $title): bool
    {
        return \in_array($title, self::TITLES_MAP, true);
    }
}
