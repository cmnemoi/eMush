<?php

declare(strict_types=1);

namespace Mush\Hunter\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class HunterEnum
{
    public const ASTEROID = 'asteroid';
    public const DICE = 'dice';
    public const HUNTER = 'hunter';
    public const SPIDER = 'spider';
    public const TRANSPORT = 'transport';
    public const SUMMER_EVENT_TRANSPORT = 'summer_event_transport';
    public const TRAX = 'trax';

    public static function getAll(): ArrayCollection
    {
        return new ArrayCollection([
            self::ASTEROID,
            self::DICE,
            self::HUNTER,
            self::SPIDER,
            self::TRANSPORT,
            self::TRAX,
        ]);
    }

    public static function getAdvancedHunters(): ArrayCollection
    {
        return new ArrayCollection([
            self::ASTEROID,
            self::DICE,
            self::SPIDER,
            self::TRANSPORT,
            self::TRAX,
        ]);
    }

    public static function getHostiles(): ArrayCollection
    {
        return new ArrayCollection([
            self::ASTEROID,
            self::DICE,
            self::HUNTER,
            self::SPIDER,
            self::TRAX,
        ]);
    }

    public static function getNonHostiles(): ArrayCollection
    {
        return new ArrayCollection([
            self::TRANSPORT,
            self::SUMMER_EVENT_TRANSPORT,
        ]);
    }

    public static function getHuntersThatDoNotStopFromAcceptingTrade(): ArrayCollection
    {
        return new ArrayCollection([
            self::TRANSPORT,
            self::SUMMER_EVENT_TRANSPORT,
            self::ASTEROID,
        ]);
    }

    public static function isTrader(string $name): bool
    {
        return \in_array($name, [
            self::TRANSPORT,
            self::SUMMER_EVENT_TRANSPORT,
        ], true);
    }
}
