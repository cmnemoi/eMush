<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Player\Entity\Player;
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
        $this->incrementRebelsStatisticForAuthor($event->getAuthorOrThrow());
        $this->incrementTeamAllRebelsForCrewIfAllRebelBasesDecoded($event);
        $this->updateTeamRebelsStatisticForCrew($event);
    }

    private function incrementRebelsStatisticForAuthor(Player $author): void
    {
        $this->updatePlayerStatisticService->execute(
            player: $author,
            statisticName: StatisticEnum::REBELS,
        );
    }

    private function incrementTeamAllRebelsForCrewIfAllRebelBasesDecoded(RebelBaseDecodedEvent $event): void
    {
        if (!$this->rebelBaseRepository->areAllRebelBasesDecoded($event->daedalusId)) {
            return;
        }

        foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: StatisticEnum::TEAM_ALL_REBELS,
            );
        }
    }

    private function updateTeamRebelsStatisticForCrew(RebelBaseDecodedEvent $event): void
    {
        $numberOfDecodedRebelBases = \count($this->rebelBaseRepository->findAllDecodedRebelBases($event->daedalusId));

        foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: StatisticEnum::TEAM_REBELS,
                count: $numberOfDecodedRebelBases
            );
        }
    }
}
