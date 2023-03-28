<?php

namespace Mush\Hunter\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class HunterEnum
{
    public const ASTEROID = 'asteroid';
    public const DICE = 'dice';
    public const HUNTER = 'hunter';
    public const SPIDER = 'spider';
    public const TRAX = 'trax';

    public static function getNormalModeHunters(): ArrayCollection
    {
        return new ArrayCollection([
            self::HUNTER,
        ]);
    }

    public static function getHardModeHunters(): ArrayCollection
    {
        return new ArrayCollection([
            self::ASTEROID,
            self::HUNTER,
            self::SPIDER,
            self::TRAX,
        ]);
    }

    public static function getVeryHardModeHunters(): ArrayCollection
    {
        return new ArrayCollection([
            self::ASTEROID,
            self::DICE,
            self::HUNTER,
            self::SPIDER,
            self::TRAX,
        ]);
    }
}
