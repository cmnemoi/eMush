<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\HolidayEnum;

class GetHolidayForDaedalusService
{
    public function __construct() {}

    public function execute(Daedalus $daedalus): string
    {
        if ($daedalus->getDaedalusConfig()->getHoliday() !== HolidayEnum::CURRENT) {
            return $daedalus->getDaedalusConfig()->getHoliday();
        }

        return $this->getCurrentHoliday();
    }

    public function getCurrentHoliday(): string
    {
        if ($this->isAnniversary()) {
            return HolidayEnum::ANNIVERSARY;
        }

        if ($this->isAprilFools()) {
            return HolidayEnum::APRIL_FOOLS;
        }

        if ($this->isHalloween()) {
            return HolidayEnum::HALLOWEEN;
        }

        return HolidayEnum::NONE;
    }

    private function isAnniversary(): bool
    {
        $currentDate = new \DateTime();

        return $currentDate->format('j') >= 3 && $currentDate->format('j') <= 24 && $currentDate->format('F') === 'January';
    }

    private function isAprilFools(): bool
    {
        $currentDate = new \DateTime();

        return $currentDate->format('j') <= 14 && $currentDate->format('F') === 'April';
    }

    private function isHalloween(): bool
    {
        $currentDate = new \DateTime();

        return ($currentDate->format('j') >= 24 && $currentDate->format('F') === 'October') || ($currentDate->format('j') <= 7 && $currentDate->format('F') === 'November');
    }
}
