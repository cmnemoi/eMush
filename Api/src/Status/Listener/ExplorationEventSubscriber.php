<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Action\Enum\ActionTypeEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    public const int DIRTY_RATE = 15;

    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => 'onExplorationStarted',
            ExplorationEvent::EXPLORATION_FINISHED => 'onExplorationFinished',
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $explorators = $exploration->getExplorators();
        $planet = $exploration->getPlanet();

        // do not block explorators if there is oxygen on the planet
        if ($planet->hasSectorByName(PlanetSectorEnum::OXYGEN)) {
            return;
        }

        $exploratorsWithoutSpaceSuit = $exploration->getExploratorsWithoutSpacesuit();

        /** @var Player $explorator */
        foreach ($exploratorsWithoutSpaceSuit as $explorator) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::STUCK_IN_THE_SHIP,
                holder: $explorator,
                tags: $event->getTags(),
                time: $event->getTime(),
                visibility: VisibilityEnum::PUBLIC,
            );
        }

        // won't do an exploration with all explorators stucked in the ship
        if ($exploratorsWithoutSpaceSuit->count() === $explorators->count()) {
            $event->stopPropagation();

            $explorationEvent = new ExplorationEvent(
                exploration: $exploration,
                tags: $event->getTags(),
                time: new \DateTime(),
            );
            $explorationEvent->addTag(ExplorationEvent::ALL_EXPLORATORS_STUCKED);
            $this->eventService->callEvent($explorationEvent, ExplorationEvent::ALL_EXPLORATORS_STUCKED);
        }
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $this->removeStuckInTheShipStatusToExplorators($event);

        if ($event->getExploration()->isAnyExploratorAlive()) {
            $this->addLootedOxygenToDaedalus($event);
            $this->addLootedFuelToDaedalus($event);
        }

        $this->deleteExplorationOxygenStatus($event);
        $this->deleteExplorationFuelStatus($event);

        $this->makeExploratorsDirty($event);
    }

    private function addLootedOxygenToDaedalus(ExplorationEvent $event): void
    {
        $daedalus = $event->getExploration()->getDaedalus();

        /** @var ChargeStatus $oxygenStatus */
        $oxygenStatus = $daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_OXYGEN);
        if ($oxygenStatus === null) {
            return;
        }

        $daedalusModifierEvent = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            $oxygenStatus->getCharge(),
            $event->getTags(),
            $event->getTime(),
        );
        $this->eventService->callEvent($daedalusModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function addLootedFuelToDaedalus(ExplorationEvent $event): void
    {
        $daedalus = $event->getExploration()->getDaedalus();

        /** @var ChargeStatus $fuelStatus */
        $fuelStatus = $daedalus->getStatusByName(DaedalusStatusEnum::EXPLORATION_FUEL);
        if ($fuelStatus === null) {
            return;
        }

        $daedalusModifierEvent = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::FUEL,
            $fuelStatus->getCharge(),
            $event->getTags(),
            $event->getTime(),
        );
        $this->eventService->callEvent($daedalusModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function deleteExplorationOxygenStatus(ExplorationEvent $event): void
    {
        $this->statusService->removeStatus(
            statusName: DaedalusStatusEnum::EXPLORATION_OXYGEN,
            holder: $event->getExploration()->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function deleteExplorationFuelStatus(ExplorationEvent $event): void
    {
        $this->statusService->removeStatus(
            statusName: DaedalusStatusEnum::EXPLORATION_FUEL,
            holder: $event->getExploration()->getDaedalus(),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
    }

    private function makeExploratorsDirty(ExplorationEvent $event): void
    {
        $explorators = $event->getExploration()->getActiveExplorators();

        // Dirtiness should not be prevented by stainproof apron
        $event->addTag(ActionTypeEnum::ACTION_SUPER_DIRTY->value);

        /** @var Player $explorator */
        foreach ($explorators as $explorator) {
            if ($this->randomService->isSuccessful(self::DIRTY_RATE)) {
                $this->statusService->createStatusFromName(
                    statusName: PlayerStatusEnum::DIRTY,
                    holder: $explorator,
                    tags: $event->getTags(),
                    time: $event->getTime(),
                    visibility: VisibilityEnum::PRIVATE,
                );
            }
        }
    }

    private function removeStuckInTheShipStatusToExplorators(ExplorationEvent $event): void
    {
        $exploratorsWithoutSpaceSuit = $event->getExploration()->getExploratorsWithoutSpacesuit();

        /** @var Player $explorator */
        foreach ($exploratorsWithoutSpaceSuit as $explorator) {
            $this->statusService->removeStatus(
                statusName: PlayerStatusEnum::STUCK_IN_THE_SHIP,
                holder: $explorator,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
        }
    }
}
