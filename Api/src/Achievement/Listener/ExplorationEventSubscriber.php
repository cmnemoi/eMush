<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Command\IncrementUserStatisticCommand;
use Mush\Achievement\Command\UpdateUserStatisticIfSuperiorCommand;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Query\GetDiscoveredArtefactsCountQuery;
use Mush\Equipment\Query\GetDiscoveredArtefactsCountQueryHandler;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ExplorationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private GetDiscoveredArtefactsCountQueryHandler $queryHandler,
    ) {}

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
        $this->incrementArtefactSpecialistStatisticFromEvent($event);
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

    private function incrementArtefactSpecialistStatisticFromEvent(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $daedalus = $event->getDaedalus();

        if ($exploration->allExploratorsAreDeadOrLost()) {
            return;
        }

        /** @var Player $explorer */
        foreach ($exploration->getNotLostActiveExplorators() as $explorer) {
            $this->commandBus->dispatch(
                new UpdateUserStatisticIfSuperiorCommand(
                    userId: $explorer->getUser()->getId(),
                    statisticName: StatisticEnum::ARTEFACT_SPECIALIST,
                    language: $daedalus->getLanguage(),
                    newValue: $this->queryHandler->execute(
                        new GetDiscoveredArtefactsCountQuery($daedalus->getId())
                    ),
                )
            );
        }
    }
}
