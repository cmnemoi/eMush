<?php

declare(strict_types=1);

namespace Mush\Achievement\Event;

use Mush\Game\Event\AbstractGameEvent;

final class StatisticIncrementedEvent extends AbstractGameEvent
{
    public function __construct(
        public readonly int $statisticId,
        public readonly int $userId,
        public readonly string $language,
        array $tags = [],
        \DateTime $dateTime = new \DateTime(),
    ) {}
}
