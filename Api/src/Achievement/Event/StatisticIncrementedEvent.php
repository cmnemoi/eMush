<?php

declare(strict_types=1);

namespace Mush\Achievement\Event;

use Mush\Game\Event\AbstractGameEvent;

final class StatisticIncrementedEvent extends AbstractGameEvent
{
    public function __construct(
        private int $statisticId,
        private string $language,
        array $tags = [],
        \DateTime $dateTime = new \DateTime(),
    ) {}

    public function getStatisticId(): int
    {
        return $this->statisticId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
