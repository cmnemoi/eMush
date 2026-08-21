<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\HolidayEnum;

class GetHolidayForDaedalusService
{
    public function __construct() {}

    public function execute(Daedalus $daedalus, \DateTime $dateTime = new \DateTime()): string
    {
        if ($daedalus->getDaedalusConfig()->getHoliday() !== HolidayEnum::CURRENT) {
            return $daedalus->getDaedalusConfig()->getHoliday();
        }

        return $this->getCurrentHoliday($dateTime);
    }

    public function getCurrentHoliday(\DateTime $dateTime): string
    {
        if ($this->isAnniversary($dateTime)) {
            return HolidayEnum::ANNIVERSARY;
        }

        if ($this->isAprilFools($dateTime)) {
            return HolidayEnum::APRIL_FOOLS;
        }

        if ($this->isHalloween($dateTime)) {
            return HolidayEnum::HALLOWEEN;
        }

        if ($this->isSummer($dateTime)) {
            return HolidayEnum::SUMMER_TREASURE_HUNT;
        }

        return HolidayEnum::NONE;
    }

    private function isAnniversary(\DateTime $dateTime): bool
    {
        return $dateTime->format('j') >= 3 && $dateTime->format('j') <= 24 && $dateTime->format('F') === 'January';
    }

    private function isAprilFools(\DateTime $dateTime): bool
    {
        return $dateTime->format('j') <= 14 && $dateTime->format('F') === 'April';
    }

    private function isHalloween(\DateTime $dateTime): bool
    {
        return ($dateTime->format('j') >= 24 && $dateTime->format('F') === 'October') || ($dateTime->format('j') <= 7 && $dateTime->format('F') === 'November');
    }

    private function isSummer(\DateTime $dateTime): bool
    {
        return ($dateTime->format('j') >= 16 && $dateTime->format('F') === 'August') || ($dateTime->format('j') <= 6 && $dateTime->format('F') === 'September');
    }
}
