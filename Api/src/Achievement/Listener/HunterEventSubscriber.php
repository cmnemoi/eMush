<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Hunter\Event\HunterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class HunterEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

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

        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $event->getAuthorOrThrow()->getUser()->getId(),
                statisticName: StatisticEnum::HUNTER_DOWN,
                language: $event->getAuthorOrThrow()->getLanguage(),
            )
        );
    }
}
