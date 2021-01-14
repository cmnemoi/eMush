<?php

namespace Mush\Daedalus\Event;

use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $daedalus = $player->getDaedalus();
        if ($daedalus->getPlayers()->count() === $daedalus->getGameConfig()->getMaxPlayer()) {
            $fullDaedalusEvent = new DaedalusEvent($daedalus);
            $this->eventDispatcher->dispatch($fullDaedalusEvent, DaedalusEvent::FULL_DAEDALUS);
        }
    }
}
