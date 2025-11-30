<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Hunter\Event\HunterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class HunterEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
        ];
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        if (!$event->hasAuthor() || $event->getHunter()->isNonHostile()) {
            return;
        }

        $this->updatePlayerStatisticService->execute(
            player: $event->getAuthorOrThrow(),
            statisticName: StatisticEnum::HUNTER_DOWN,
        );
    }
}
