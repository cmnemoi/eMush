<?php

declare(strict_types=1);

namespace Mush\Triumph\Service;

use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Event\TriumphChangedEvent;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Repository\TriumphConfigRepositoryInterface;

final class ChangeTriumphFromEventService
{
    public function __construct(
        private EventServiceInterface $eventService,
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
                fn (Player $player) => $this->addTriumphToPlayer($triumphConfig, $player)
            );
        }
    }

    private function addTriumphToPlayer(TriumphConfig $triumphConfig, Player $player): void
    {
        $player->addTriumph($triumphConfig->getQuantity());

        $this->eventService->callEvent(
            new TriumphChangedEvent($player, $triumphConfig),
            TriumphChangedEvent::class,
        );
    }
}
