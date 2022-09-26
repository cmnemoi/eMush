<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Event\Service\EventService;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private EventService $eventService;

    public function __construct(EventService $eventService, PlayerServiceInterface $playerService)
    {
          $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
            PlayerCycleEvent::PLAYER_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        foreach ($player->getEquipments() as $item) {
            $itemNewCycle = new EquipmentCycleEvent(
                $item,
                $player->getDaedalus(),
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->callEvent($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }
    }

    public function onNewDay(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        foreach ($player->getEquipments() as $item) {
            $itemNewDay = new EquipmentCycleEvent(
                $item,
                $player->getDaedalus(),
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->callEvent($itemNewDay, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);
        }
    }
}
