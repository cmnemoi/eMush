<?php

namespace Mush\Game\ConfigData;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;

class TitleConfigData
{
    public static array $dataArray = [
        ['name' => TitleEnum::COMMANDER, 'priority' => [CharacterEnum::JIN_SU, CharacterEnum::CHAO, CharacterEnum::GIOELE, CharacterEnum::STEPHEN, CharacterEnum::FRIEDA, CharacterEnum::KUAN_TI, CharacterEnum::HUA, CharacterEnum::DEREK, CharacterEnum::ROLAND, CharacterEnum::RALUCA, CharacterEnum::FINOLA, CharacterEnum::PAOLA, CharacterEnum::TERRENCE, CharacterEnum::ELEESHA, CharacterEnum::ANDIE, CharacterEnum::IAN, CharacterEnum::JANICE, CharacterEnum::CHUN]],
        ['name' => TitleEnum::NERON_MANAGER, 'priority' => [CharacterEnum::JANICE, CharacterEnum::TERRENCE, CharacterEnum::ELEESHA, CharacterEnum::RALUCA, CharacterEnum::FINOLA, CharacterEnum::ANDIE, CharacterEnum::FRIEDA, CharacterEnum::IAN, CharacterEnum::STEPHEN, CharacterEnum::PAOLA, CharacterEnum::JIN_SU, CharacterEnum::HUA, CharacterEnum::KUAN_TI, CharacterEnum::GIOELE, CharacterEnum::CHUN, CharacterEnum::ROLAND, CharacterEnum::CHAO, CharacterEnum::DEREK]],
        ['name' => TitleEnum::COM_MANAGER, 'priority' => [CharacterEnum::PAOLA, CharacterEnum::ELEESHA, CharacterEnum::ANDIE, CharacterEnum::STEPHEN, CharacterEnum::JANICE, CharacterEnum::ROLAND, CharacterEnum::HUA, CharacterEnum::DEREK, CharacterEnum::JIN_SU, CharacterEnum::KUAN_TI, CharacterEnum::GIOELE, CharacterEnum::CHUN, CharacterEnum::IAN, CharacterEnum::FINOLA, CharacterEnum::TERRENCE, CharacterEnum::FRIEDA, CharacterEnum::CHAO, CharacterEnum::RALUCA]],
    ];
}
