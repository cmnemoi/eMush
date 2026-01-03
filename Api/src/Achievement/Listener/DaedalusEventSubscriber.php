<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\UpdateUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\PublishPendingStatisticsService;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\User\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private PublishPendingStatisticsService $publishPendingStatisticsService,
        private UpdatePlayerStatisticService $updatePlayerStatisticService,
        private UserRepositoryInterface $userRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::LOWEST],
            DaedalusEvent::FINISH_DAEDALUS => [
                ['onDaedalusFinish', EventPriorityEnum::HIGHEST],
                ['afterDaedalusFinish', EventPriorityEnum::LOWEST],
            ],
        ];
    }

    public function onDaedalusNewCycle(DaedalusCycleEvent $event): void
    {
        if ($event->doesNotHaveTag(EventEnum::NEW_DAY)) {
            return;
        }

        $daedalus = $event->getDaedalus();

        foreach ($daedalus->getAlivePlayers() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: StatisticEnum::fromDaedalusDay($daedalus->getDay()),
            );

            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: StatisticEnum::DAY_MAX,
                count: $daedalus->getDay()
            );
        }
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $this->incrementEndCauseStatisticFromEvent($event);
        $this->incrementCharacterCyclesStatisticFromEvent($event);
    }

    public function afterDaedalusFinish(DaedalusEvent $event): void
    {
        $closedDaedalus = $event->getDaedalus()->getDaedalusInfo()->getClosedDaedalus();
        $this->publishPendingStatisticsService->fromClosedDaedalus($closedDaedalus->getId());

        /** @var ClosedPlayer $player */
        foreach ($closedDaedalus->getPlayers() as $player) {
            $this->commandBus->dispatch(
                new UpdateUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: StatisticEnum::TRIUMPH,
                    language: $closedDaedalus->getLanguage(),
                    count: $player->getTriumph() ?? 0
                )
            );
        }
    }

    private function incrementEndCauseStatisticFromEvent(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $language = $daedalus->getLanguage();

        /** @var Player $player */
        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            $this->commandBus->dispatch(
                new UpdateUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: $this->getPlayerStatisticToIncrementFromEvent($player, $event),
                    language: $language,
                )
            );
        }
    }

    // deprecated
    private function incrementCharacterCyclesStatisticFromEvent(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $language = $daedalus->getLanguage();

        // Collect processed users to avoid processing duplicates
        $processedUserIds = [];

        /** @var Player $player */
        foreach ($daedalus->getPlayers() as $player) {
            $user = $player->getUser();
            $userId = $user->getId();

            // Skip if already processed
            if (isset($processedUserIds[$userId])) {
                continue;
            }

            // Mark user as processed
            $processedUserIds[$userId] = true;

            // Increment statistics for all characters the user played
            foreach ($user->getCycleCounts()->toArray() as $character => $cycleCount) {
                $this->commandBus->dispatch(
                    new UpdateUserStatisticCommand(
                        userId: $userId,
                        statisticName: StatisticEnum::getCyclesStatFromCharacterName($character),
                        language: $language,
                        count: $cycleCount
                    )
                );
            }

            // Reset user cycle counts to avoid accumulating cycles across multiple games
            $user->resetCycleCounts();
            $this->userRepository->save($user);
        }
    }

    private function getPlayerStatisticToIncrementFromEvent(Player $player, DaedalusEvent $event): StatisticEnum
    {
        return match ($event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP)) {
            EndCauseEnum::EDEN => $player->isMush() ? StatisticEnum::EDEN_CONTAMINATED : StatisticEnum::EDEN,
            EndCauseEnum::SOL_RETURN => StatisticEnum::BACK_TO_ROOT,
            default => StatisticEnum::NULL,
        };
    }
}
