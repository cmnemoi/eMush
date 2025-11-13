<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class RebelBaseDecodedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private RebelBaseRepositoryInterface $rebelBaseRepository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            RebelBaseDecodedEvent::class => 'onRebelBaseDecoded',
        ];
    }

    public function onRebelBaseDecoded(RebelBaseDecodedEvent $event): void
    {
        $this->commandBus->dispatch(
            new IncrementUserStatisticCommand(
                userId: $event->getAuthorUserId(),
                statisticName: StatisticEnum::REBELS,
                language: $event->getLanguage(),
            )
        );

        if ($this->rebelBaseRepository->areAllRebelBasesDecoded($event->daedalusId)) {
            foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
                $this->commandBus->dispatch(
                    new IncrementUserStatisticCommand(
                        userId: $player->getUser()->getId(),
                        statisticName: StatisticEnum::TEAM_ALL_REBELS,
                        language: $player->getLanguage(),
                    )
                );
            }
        }
    }
}
