<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
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
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            DaedalusEvent::TRAVEL_LAUNCHED => 'onTravelLaunched',
        ];
    }

    public function onDaedalusFull(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->neronMessageService->createNeronMessage(NeronMessageEnum::START_GAME, $daedalus, [], $event->getTime());
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $this->neronMessageService->createNeronMessage(NeronMessageEnum::TRAVEL_DEFAULT, $daedalus, [], $event->getTime());
    }
}
