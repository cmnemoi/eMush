<?php

namespace Mush\Status\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterCycleEvent;
use Mush\Place\Event\PlaceCycleEvent;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusHolderCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => ['onNewCycleEquipment', 10],
            HunterCycleEvent::HUNTER_NEW_CYCLE => 'onNewCycleHunter',
            PlaceCycleEvent::PLACE_NEW_CYCLE => 'onNewCyclePlace',
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCyclePlayer',
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => 'onNewCycleDaedalus',
        ];
    }

    public function onNewCycleEquipment(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $this->triggerStatusCycleEvent($equipment, $event->getTags(), $event->getTime());
    }

    public function onNewCycleHunter(HunterCycleEvent $event): void
    {
        $attackingHunters = $event->getDaedalus()->getAttackingHunters();

        foreach ($attackingHunters as $hunter) {
            $this->triggerStatusCycleEvent($hunter, $event->getTags(), $event->getTime());
        }
    }

    public function onNewCyclePlace(PlaceCycleEvent $event): void
    {
        $place = $event->getPlace();
        $this->triggerStatusCycleEvent($place, $event->getTags(), $event->getTime());
    }

    public function onNewCyclePlayer(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();
        $this->triggerStatusCycleEvent($player, $event->getTags(), $event->getTime());
    }

    public function onNewCycleDaedalus(DaedalusCycleEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->triggerStatusCycleEvent($daedalus, $event->getTags(), $event->getTime());
    }

    public function triggerStatusCycleEvent(
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time
    ): void {
        /** @var Status $status */
        foreach ($holder->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $holder,
                $tags,
                $time
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }
    }
}
