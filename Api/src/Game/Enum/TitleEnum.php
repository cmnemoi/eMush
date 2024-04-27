<?php

namespace Mush\Game\Enum;

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
}
