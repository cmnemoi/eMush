<?php

declare(strict_types=1);

namespace Mush\Triumph\Service;

use Mush\Player\Entity\Player;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Repository\TriumphConfigRepositoryInterface;

final class ChangeTriumphFromEventService
{
    public function __construct(
        private TriumphConfigRepositoryInterface $triumphConfigRepository,
    ) {}

    public function execute(TriumphSourceEventInterface $event): void
    {
        $triumphConfigs = $this->triumphConfigRepository->findAllByTargetedEvent($event);

        foreach ($triumphConfigs as $triumphConfig) {
            if (!$event->hasExpectedTags($triumphConfig)) {
                continue;
            }

            $event->getTargetsForTriumph($triumphConfig)->map(
                static fn (Player $player) => $player->addTriumph($triumphConfig->getQuantity())
            );
        }
    }
}
