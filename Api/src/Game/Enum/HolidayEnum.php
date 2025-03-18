<?php

namespace Mush\Game\Enum;

abstract class HolidayEnum
{
    public const string CURRENT = 'current'; // CONFIGDATA ONLY: select holiday based on current real life date
    public const string NONE = 'none'; // CONFIGDATA ONLY: force no holiday
    public const string ANNIVERSARY = 'anniversary';
    public const string HALLOWEEN = 'halloween';
    public const string APRIL_FOOLS = 'april_fools';
}
