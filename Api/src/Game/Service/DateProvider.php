<?php

declare(strict_types=1);

namespace Mush\Game\Service;

final class DateProvider implements DateProviderInterface
{
    public function now(): \DateTime
    {
        return new \DateTime();
    }
}
