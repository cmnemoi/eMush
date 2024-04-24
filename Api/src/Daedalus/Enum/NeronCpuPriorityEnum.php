<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

use Mush\Status\Enum\DaedalusStatusEnum;

/** @codeCoverageIgnore */
abstract class NeronCpuPriorityEnum
{
    public const string NONE = 'none';
    public const string ASTRONAVIGATION = 'astronavigation';
    public const string DEFENCE = 'defence';
    public const string PILGRED = 'pilgred';
    public const string PROJECTS = 'projects';

    public static array $statusMap = [
        self::ASTRONAVIGATION => DaedalusStatusEnum::ASTRONAVIGATION_NERON_CPU_PRIORITY,
        self::DEFENCE => DaedalusStatusEnum::DEFENCE_NERON_CPU_PRIORITY,
    ];

    public static function getAll(): array
    {
        return [
            self::NONE,
            self::ASTRONAVIGATION,
            self::DEFENCE,
            self::PILGRED,
            self::PROJECTS,
        ];
    }
}
