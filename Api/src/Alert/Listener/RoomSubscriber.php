<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertService;
use Mush\Place\Event\RoomEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoomSubscriber implements EventSubscriberInterface
{
    private AlertService $alertService;

    public function __construct(
        AlertService $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RoomEvent::STARTING_FIRE => 'onStartingFire',
            RoomEvent::STOP_FIRE => 'onStopFire',
            RoomEvent::TREMOR => ['onTremor', 1000],
        ];
    }

    public function onStartingFire(RoomEvent $event): void
    {
        $this->alertService->handleFireStart($event->getPlace());
    }

    public function onStopFire(RoomEvent $event): void
    {
        $this->alertService->handleFireStop($event->getPlace());
    }

    public function onTremor(RoomEvent $event): void
    {
        $gravityAlert = $this->alertService->findByNameAndDaedalus(AlertEnum::NO_GRAVITY, $event->getPlace()->getDaedalus());
        if ($gravityAlert !== null) {
            $event->setIsGravity(false);
        }
    }
}
