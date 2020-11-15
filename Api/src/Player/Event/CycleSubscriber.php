<?php

namespace Mush\Player\Event;

use Mush\Game\Event\CycleEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private PlayerServiceInterface $playerService;

    public function __construct(EventDispatcherInterface $eventDispatcher, PlayerServiceInterface $playerService)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->playerService = $playerService;
    }

    public static function getSubscribedEvents()
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event)
    {
        if (!($player = $event->getPlayer())) {
            return;
        }

        $this->playerService->handleNewCycle($player, $event->getTime());

        foreach ($player->getItems() as $item) {
            $itemNewCycle = new CycleEvent($player->getDaedalus(), $event->getTime());
            $itemNewCycle->setGameItem($item);
            $this->eventDispatcher->dispatch($itemNewCycle, CycleEvent::NEW_CYCLE);
        }
    }
}
