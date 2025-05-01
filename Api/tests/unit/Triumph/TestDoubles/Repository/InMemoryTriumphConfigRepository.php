<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\TestDoubles\Repository;

use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Repository\TriumphConfigRepositoryInterface;

final class InMemoryTriumphConfigRepository implements TriumphConfigRepositoryInterface
{
    /** @var TriumphConfig[] */
    private array $triumphConfigs = [];

    public function findAllByTargetedEvent(TriumphSourceEventInterface $targetedEvent): array
    {
        foreach ($this->triumphConfigs as $triumphConfig) {
            if ($triumphConfig->getTargetedEvent() === $targetedEvent->getEventName()) {
                $triumphConfigs[] = $triumphConfig;
            }
        }

        return $triumphConfigs;
    }

    public function save(TriumphConfig $triumphConfig): void
    {
        $this->triumphConfigs[] = $triumphConfig;
    }

    public function clear(): void
    {
        $this->triumphConfigs = [];
    }
}
