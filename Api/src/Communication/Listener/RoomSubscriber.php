<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Place\Event\RoomEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService
    ) {
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEventInterface::STARTING_FIRE => 'onStartingFire',
        ];
    }

    public function onStartingFire(RoomEventInterface $event): void
    {
        $daedalus = $event->getPlace()->getDaedalus();

        $this->neronMessageService->createNewFireMessage($daedalus, $event->getTime());
    }
}
