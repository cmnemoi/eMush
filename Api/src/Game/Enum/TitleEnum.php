<?php

namespace Mush\Game\Enum;

class TitleEnum
{
    public const COMMANDER = 'commander';
    public const NERON_MANAGER = 'neron_manager';
    public const COM_MANAGER = 'com_manager';

    public const TITLES_MAP = [
        self::COMMANDER => self::COMMANDER,
        self::NERON_MANAGER => self::NERON_MANAGER,
        self::COM_MANAGER => self::COM_MANAGER,
    ];
}
