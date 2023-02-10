<?php

namespace Mush\Game\ConfigData;

use Mush\Game\Enum\LanguageEnum;

class LocalizationConfigData
{
    public static array $dataArray = [
        [
            'name' => LanguageEnum::FRENCH,
            'timeZone' => 'Europe/Paris',
            'language' => LanguageEnum::FRENCH,
        ],
        [
            'name' => LanguageEnum::ENGLISH,
            'timeZone' => 'UTC',
            'language' => LanguageEnum::ENGLISH,
        ],
    ];
}
