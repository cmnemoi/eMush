<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

use Mush\Status\Enum\DaedalusStatusEnum;

/** @codeCoverageIgnore */
final class NeronCpuPriorityEnum
{
    public const NONE = 'none';
    public const ASTRONAVIGATION = 'astronavigation';

    public static array $statusMap = [
        self::ASTRONAVIGATION => DaedalusStatusEnum::ASTRONAVIGATION_NERON_CPU_PRIORITY,
    ];

    public static function getAll(): array
    {
        return [
            self::NONE,
            self::ASTRONAVIGATION,
        ];
    }
}
