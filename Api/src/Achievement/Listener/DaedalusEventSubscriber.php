<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class DaedalusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::LOWEST],
            DaedalusEvent::FINISH_DAEDALUS => ['onDaedalusFinish', EventPriorityEnum::HIGHEST],
        ];
    }

    public function onDaedalusNewCycle(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        foreach ($daedalus->getAlivePlayers() as $player) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: StatisticEnum::fromDaedalusDate($daedalus->getGameDate()),
                    language: $daedalus->getLanguage(),
                )
            );
        }
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $this->incrementEndCauseStatisticFromEvent($event);
        $this->incrementHumanCyclesStatisticFromEvent($event);
    }

    private function incrementEndCauseStatisticFromEvent(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $language = $daedalus->getLanguage();

        /** @var Player $player */
        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: $this->getPlayerStatisticToIncrementFromEvent($player, $event),
                    language: $language,
                )
            );
        }
    }

    private function incrementHumanCyclesStatisticFromEvent(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $language = $daedalus->getLanguage();

        /** @var Player $player */
        foreach ($daedalus->getPlayers() as $player) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: StatisticEnum::fromOrNull($player->getName()),
                    language: $language,
                    increment: $player->getPlayerInfo()->getHumanCyclesCount(),
                )
            );
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: StatisticEnum::MUSH_CYCLES,
                    language: $language,
                    increment: $player->getPlayerInfo()->getMushCyclesCount(),
                )
            );
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
