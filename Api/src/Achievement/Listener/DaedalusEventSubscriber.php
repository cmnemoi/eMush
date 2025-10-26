<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
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
            DaedalusEvent::FINISH_DAEDALUS => ['onDaedalusFinish', EventPriorityEnum::HIGHEST],
        ];
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $this->incrementEndCauseStatisticFromEvent($event);
        $this->incrementHumanCyclesStatisticFromEvent($event);
    }

    private function incrementEndCauseStatisticFromEvent(DaedalusEvent $event): void
    {
        $endCause = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);
        $statisticName = match ($endCause) {
            EndCauseEnum::SOL_RETURN => StatisticEnum::BACK_TO_ROOT,
            default => StatisticEnum::NULL,
        };

        $daedalus = $event->getDaedalus();
        $language = $daedalus->getLanguage();

        /** @var Player $player */
        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $player->getUser()->getId(),
                    statisticName: $statisticName,
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
                    statisticName: StatisticEnum::tryFrom($player->getName()) ?? StatisticEnum::NULL,
                    language: $language,
                    increment: $player->getPlayerInfo()->getHumanCyclesCount(),
                )
            );
        }
    }
}
