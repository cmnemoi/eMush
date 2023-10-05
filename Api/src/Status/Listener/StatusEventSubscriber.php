<?php

declare(strict_types=1);

namespace Mush\Status\Listener;

use Mush\Daedalus\Enum\DaedalusStatusEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StatusEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        if ($event->getStatusName() === DaedalusStatusEnum::TRAVELING) {
            $daedalusEvent = new DaedalusEvent(
                $event->getDaedalus(),
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::TRAVEL_FINISHED);
        }
    }
}
