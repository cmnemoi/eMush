<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class RebelBaseDecodedEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UpdatePlayerStatisticService $updatePlayerStatisticService,
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
        $this->updatePlayerStatisticService->execute(
            player: $event->getAuthorOrThrow(),
            statisticName: StatisticEnum::REBELS,
        );

        if ($this->rebelBaseRepository->areAllRebelBasesDecoded($event->daedalusId)) {
            foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
                $this->updatePlayerStatisticService->execute(
                    player: $player,
                    statisticName: StatisticEnum::TEAM_ALL_REBELS,
                );
            }
        }
    }
}
