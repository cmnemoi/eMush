<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Event\XylophEntryDecodedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class XylophEntryDecodedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

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
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: $statisticName,
                    language: $event->getLanguage(),
                )
            );
        }
    }
}
