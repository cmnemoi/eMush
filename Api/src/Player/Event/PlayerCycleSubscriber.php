<?php

namespace Mush\Player\Event;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private PlayerServiceInterface $playerService;

    public function __construct(EventDispatcherInterface $eventDispatcher, PlayerServiceInterface $playerService)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->playerService = $playerService;
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

        $this->playerService->handleNewCycle($player, $event->getTime());

        foreach ($player->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent($status, $player, $player->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }

        foreach ($player->getItems() as $item) {
            $itemNewCycle = new EquipmentCycleEvent($item, $player->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }
    }

    public function onNewDay(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        $this->playerService->handleNewDay($player, $event->getTime());

        foreach ($player->getItems() as $item) {
            $itemNewDay = new EquipmentCycleEvent($item, $player->getDaedalus(), $event->getTime());
            $this->eventDispatcher->dispatch($itemNewDay, EquipmentCycleEvent::EQUIPMENT_NEW_DAY);
        }
    }
}
