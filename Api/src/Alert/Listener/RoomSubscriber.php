<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertService;
use Mush\Place\Event\RoomEventInterface;
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
            RoomEventInterface::STARTING_FIRE => 'onStartingFire',
            RoomEventInterface::STOP_FIRE => 'onStopFire',
            RoomEventInterface::TREMOR => ['onTremor', 1000],
        ];
    }

    public function onStartingFire(RoomEventInterface $event): void
    {
        $this->alertService->handleFireStart($event->getPlace());
    }

    public function onStopFire(RoomEventInterface $event): void
    {
        $this->alertService->handleFireStop($event->getPlace());
    }

    public function onTremor(RoomEventInterface $event): void
    {
        $gravityAlert = $this->alertService->findByNameAndDaedalus(AlertEnum::NO_GRAVITY, $event->getPlace()->getDaedalus());
        if ($gravityAlert !== null) {
            $event->setIsGravity(false);
        }
    }
}
