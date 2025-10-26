<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ExplorationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ExplorationEvent::EXPLORATION_FINISHED => ['onExplorationFinished', EventPriorityEnum::HIGHEST],
        ];
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $this->incrementExploFeedStatisticFromEvent($event);
        $this->incrementExplorerStatisticFromEvent($event);
    }

    private function incrementExploFeedStatisticFromEvent(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        if ($exploration->allExploratorsAreDeadOrLost()) {
            return;
        }

        $daedalus = $event->getDaedalus();
        $broughtFood = $daedalus->getPlanetPlace()->getEquipments()->filter(
            static fn (GameEquipment $equipment) => $equipment->hasMechanicByName(EquipmentMechanicEnum::RATION)
        );
        if ($broughtFood->isEmpty()) {
            return;
        }

        foreach ($exploration->getNotLostActiveExplorators() as $explorator) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $explorator->getUser()->getId(),
                    statisticName: StatisticEnum::EXPLO_FEED,
                    language: $daedalus->getLanguage(),
                    increment: $broughtFood->count(),
                )
            );
        }
    }

    private function incrementExplorerStatisticFromEvent(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $language = $exploration->getDaedalus()->getLanguage();

        foreach ($exploration->getNotLostActiveExplorators() as $explorator) {
            $this->commandBus->dispatch(
                new IncrementUserStatisticCommand(
                    userId: $explorator->getUser()->getId(),
                    statisticName: StatisticEnum::EXPLORER,
                    language: $language,
                )
            );
        }
    }
}
