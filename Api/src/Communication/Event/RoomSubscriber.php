<?php

namespace Mush\Communication\Event;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Place\Event\RoomEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private MessageServiceInterface $messageService;

    public function __construct(
        MessageServiceInterface $messageService
    ) {
        $this->messageService = $messageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::STARTING_FIRE => 'onStartingFire',
        ];
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $daedalus = $event->getRoom()->getDaedalus();

        $parentMessage = $this->messageService->getMessageNeronCycleFailures($daedalus);

        $this->messageService->createNeronMessage(NeronMessageEnum::NEW_FIRE, $daedalus, [], $event->getTime(), $parentMessage);
    }
}
