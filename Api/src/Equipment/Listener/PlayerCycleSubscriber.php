<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher, PlayerServiceInterface $playerService)
    {
          $this->eventService = $eventDispatcher;
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
            $this->eventService->dispatch($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
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
            $this->eventService->dispatch($itemNewDay, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);
        }
    }
}
