<?php

declare(strict_types=1);

namespace Mush\MetaGame\Enum;

use Mush\Game\Enum\LanguageEnum;

final class TwinoidURLEnum
{
    public const MUSH_VG = 'http://mush.vg/';
    public const MUSH_TWINOID_COM = 'http://mush.twinoid.com/';
    public const MUSH_TWINOID_ES = 'http://mush.twinoid.es/';

    public const TWINOID_TOKEN = 'https://twinoid.com/oauth/token';
    public const TWINOID_API_ME_ENDPOINT = 'https://twinoid.com/graph/me';

    public static function getMushServerFromLanguage(string $language): string
    {
        return match ($language) {
            LanguageEnum::FRENCH => self::MUSH_VG,
            LanguageEnum::ENGLISH => self::MUSH_TWINOID_COM,
            LanguageEnum::SPANISH => self::MUSH_TWINOID_ES,
            default => self::MUSH_VG,
        };
    }
}
