<?php

declare(strict_types=1);

namespace Mush\Triumph\Listener;

use Mush\Action\Event\ActionEvent;
use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Communications\Event\RebelBaseDecodedEvent;
use Mush\Communications\Event\XylophEntryDecodedEvent;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Project\Event\ProjectEvent;
use Mush\Status\Event\StatusEvent;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TriumphSourceEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChangeTriumphFromEventService $changeTriumphFromEventService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::RESULT_ACTION => 'onResultAction',
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::PLAYER_TRIUMPH],
            DaedalusEvent::FINISH_DAEDALUS => ['onDaedalusFinish', EventPriorityEnum::HIGH],
            DaedalusEvent::FULL_DAEDALUS => ['onDaedalusFull', EventPriorityEnum::LOW],
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
            EquipmentEvent::EQUIPMENT_CREATED => 'onEquipmentCreated',
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            ExplorationEvent::EXPLORATION_STARTED => ['onExplorationStarted', EventPriorityEnum::VERY_LOW],
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
            LinkWithSolEstablishedEvent::class => 'onLinkWithSolEstablished',
            PlanetSectorEvent::PLANET_SECTOR_EVENT => 'onPlanetSectorEvent',
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            PlayerEvent::INFECTION_PLAYER => 'onInfectionPlayer',
            ProjectEvent::PROJECT_ADVANCED => 'onProjectAdvanced',
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
            RebelBaseDecodedEvent::class => 'onRebelBaseDecoded',
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
            XylophEntryDecodedEvent::class => 'onXylophEntryDecoded',
        ];
    }

    public function onResultAction(ActionEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onDaedalusNewCycle(DaedalusCycleEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onDaedalusFinish(DaedalusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onLinkWithSolEstablished(LinkWithSolEstablishedEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onPlanetSectorEvent(PlanetSectorEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onInfectionPlayer(PlayerEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onProjectAdvanced(ProjectEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onRebelBaseDecoded(RebelBaseDecodedEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onXylophEntryDecoded(XylophEntryDecodedEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }
}
