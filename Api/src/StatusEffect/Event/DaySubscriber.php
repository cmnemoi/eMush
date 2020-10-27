<?php

namespace Mush\StatusEffect\Event;

use Mush\Game\Event\DayEvent;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [];
    }
}
