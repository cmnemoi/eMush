<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

use Mush\Status\Enum\DaedalusStatusEnum;

/** @codeCoverageIgnore */
abstract class NeronCpuPriorityEnum
{
    public const string NONE = 'none';
    public const string ASTRONAVIGATION = 'astronavigation';
    public const string PROJECTS = 'projects';
    public const string RESEARCH = 'research';

    public static array $statusMap = [
        self::ASTRONAVIGATION => DaedalusStatusEnum::ASTRONAVIGATION_NERON_CPU_PRIORITY,
    ];

    public static function getAll(): array
    {
        return [
            self::NONE,
            self::ASTRONAVIGATION,
            self::PROJECTS,
            self::RESEARCH,
        ];
    }

    public static function getAllExcept(string $cpuPriority): array
    {
        return array_diff(self::getAll(), [$cpuPriority]);
    }
}
