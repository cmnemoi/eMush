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

    public const array TITLES_OPPORTUNIST_STATUSES_MAP = [
        self::COMMANDER => PlayerStatusEnum::HAS_USED_OPPORTUNIST_AS_COMMANDER,
        self::NERON_MANAGER => PlayerStatusEnum::HAS_USED_OPPORTUNIST_AS_NERON_MANAGER,
        self::COM_MANAGER => PlayerStatusEnum::HAS_USED_OPPORTUNIST_AS_COM_MANAGER,
    ];
}
