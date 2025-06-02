<?php

declare(strict_types=1);

namespace Mush\Triumph\Listener;

use Mush\Communications\Event\LinkWithSolEstablishedEvent;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventPriorityEnum;
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
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onDaedalusNewCycle', EventPriorityEnum::PLAYER_TRIUMPH],
            DaedalusEvent::FINISH_DAEDALUS => ['onDaedalusFinish', EventPriorityEnum::HIGH],
            DaedalusEvent::FULL_DAEDALUS => ['onDaedalusFull', EventPriorityEnum::LOW],
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            ExplorationEvent::EXPLORATION_STARTED => ['onExplorationStarted', EventPriorityEnum::VERY_LOW],
            LinkWithSolEstablishedEvent::class => 'onLinkWithSolEstablished',
            PlanetSectorEvent::PLANET_SECTOR_EVENT => 'onPlanetSectorEvent',
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
            ProjectEvent::PROJECT_ADVANCED => 'onProjectAdvanced',
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
        ];
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

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    public function onExplorationStarted(ExplorationEvent $event): void
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

    public function onDeathPlayer(PlayerEvent $event): void
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

    public function onStatusApplied(StatusEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }
}
