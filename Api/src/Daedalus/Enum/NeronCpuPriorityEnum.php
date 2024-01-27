<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

/** @codeCoverageIgnore */
final class NeronCpuPriorityEnum
{
    public const NONE = 'none';
    public const ASTRONAVIGATION = 'astronavigation';

    public static function getAll(): array
    {
        return [
            self::NONE,
            self::ASTRONAVIGATION,
        ];
    }
}
