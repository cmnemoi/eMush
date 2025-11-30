<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Event\XylophEntryDecodedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class XylophEntryDecodedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            XylophEntryDecodedEvent::class => 'onXylophEntryDecoded',
        ];
    }

    public function onXylophEntryDecoded(XylophEntryDecodedEvent $event): void
    {
        $statisticName = match ($event->entryName) {
            XylophEnum::DISK => StatisticEnum::MUSH_GENOME,
            XylophEnum::KIVANC => StatisticEnum::KIVANC_CONTACTED,
            default => StatisticEnum::NULL,
        };

        foreach ($event->getAlivePlayers() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: $statisticName,
            );
        }
    }
}
