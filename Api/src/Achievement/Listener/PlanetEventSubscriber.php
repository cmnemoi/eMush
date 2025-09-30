<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Exploration\Event\PlanetCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class PlanetEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlanetCreatedEvent::class => 'onPlanetCreated',
        ];
    }

    public function onPlanetCreated(PlanetCreatedEvent $event): void
    {
        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $event->getAuthorUserId(),
                statisticName: StatisticEnum::PLANET_SCANNED,
                language: $event->getLanguage(),
            )
        );
    }
}
