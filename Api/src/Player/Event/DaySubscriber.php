<?php

namespace Mush\Player\Event;

use Mush\Game\Event\DayEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
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
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event): void
    {
        if (!($player = $event->getPlayer())) {
            return;
        }

        $this->playerService->handleNewDay($player, $event->getTime());

        foreach ($player->getItems() as $item) {
            $itemNewDay = new DayEvent($player->getDaedalus(), $event->getTime());
            $itemNewDay->setGameEquipment($item);
            $this->eventDispatcher->dispatch($itemNewDay, DayEvent::NEW_DAY);
        }
    }
}
