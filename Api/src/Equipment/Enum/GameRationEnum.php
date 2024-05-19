<?php

declare(strict_types=1);

namespace Mush\Equipment\Enum;

use Doctrine\Common\Collections\ArrayCollection;

abstract class GameRationEnum
{
    public const LOMBRICK_BAR = 'lombrick_bar';
    public const ORGANIC_WASTE = 'organic_waste';
    public const PROACTIVE_PUFFED_RICE = 'proactive_puffed_rice';
    public const ALIEN_STEAK = 'alien_steak';
    public const ANABOLIC = 'anabolic';
    public const COFFEE = 'coffee';
    public const COOKED_RATION = 'cooked_ration';
    public const SPACE_POTATO = 'space_potato';
    public const STANDARD_RATION = 'standard_ration';
    public const SUPERVITAMIN_BAR = 'supervitamin_bar';

    public static function getAll(): ArrayCollection
    {
        return new ArrayCollection([
            self::LOMBRICK_BAR,
            self::ORGANIC_WASTE,
            self::PROACTIVE_PUFFED_RICE,
            self::ALIEN_STEAK,
            self::ANABOLIC,
            self::COFFEE,
            self::COOKED_RATION,
            self::SPACE_POTATO,
            self::STANDARD_RATION,
            self::SUPERVITAMIN_BAR,
        ]);
    }
}
