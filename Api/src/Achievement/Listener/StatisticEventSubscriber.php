<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\UnlockStatisticAchievementCommand;
use Mush\Achievement\Event\StatisticIncrementedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class StatisticEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatisticIncrementedEvent::class => 'onStatisticIncremented',
        ];
    }

    public function onStatisticIncremented(StatisticIncrementedEvent $event): void
    {
        $this->commandBus->dispatch(UnlockStatisticAchievementCommand::fromEvent($event));
    }
}
