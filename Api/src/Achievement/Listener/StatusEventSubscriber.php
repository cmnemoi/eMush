<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class StatusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        if ($event->getStatusName() === PlayerStatusEnum::GAGGED) {
            $player = $event->getPlayerStatusHolder();

            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: StatisticEnum::GAGGED,
                    language: $player->getLanguage(),
                )
            );
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $author = $event->getAuthor();
        if (!$author) {
            return;
        }

        $statisticName = match ($event->getStatusName()) {
            StatusEnum::FIRE => StatisticEnum::EXTINGUISH_FIRE,
            default => StatisticEnum::NULL,
        };

        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $author->getUser()->getId(),
                statisticName: $statisticName,
                language: $author->getLanguage(),
            )
        );
    }
}
