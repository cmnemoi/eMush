<?php

namespace Mush\Hunter\Listener;

use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusInitSubscriber implements EventSubscriberInterface
{
    private const NB_STARTING_HUNTERS = 4;

    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusInitEvent::NEW_DAEDALUS => 'onNewDaedalus',
        ];
    }

    public function onNewDaedalus(DaedalusInitEvent $event): void
    {
        $unpoolHunterEvent = new HunterPoolEvent(
            $event->getDaedalus(),
            nbHunters: self::NB_STARTING_HUNTERS,
            tags: [EventEnum::CREATE_DAEDALUS],
            time: $event->getTime()
        );

        $this->eventService->callEvent($unpoolHunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }
}
