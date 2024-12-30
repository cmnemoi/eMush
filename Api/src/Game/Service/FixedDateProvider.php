<?php

declare(strict_types=1);

namespace Mush\Game\Service;

final class FixedDateProvider implements DateProviderInterface
{
    private \DateTime $now;

    public function __construct(\DateTime $now)
    {
        $this->now = $now;
    }

    public function now(): \DateTime
    {
        return clone $this->now;
    }
}
