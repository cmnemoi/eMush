<?php

declare(strict_types=1);

namespace Mush\Achievement\Command;

use Mush\Achievement\Event\StatisticIncrementedEvent;

final readonly class UnlockStatisticAchievementCommand
{
    public function __construct(
        public readonly int $statisticId,
        public readonly string $language,
    ) {}

    public static function fromEvent(StatisticIncrementedEvent $event): self
    {
        return new self($event->getStatisticId(), $event->getLanguage());
    }
}
