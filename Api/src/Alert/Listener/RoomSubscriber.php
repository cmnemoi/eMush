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
            RoomEvent::TREMOR => ['onTremor', 1000],
        ];
    }

    public function onTremor(RoomEvent $event): void
    {
        $gravityAlert = $this->alertService->findByNameAndDaedalus(AlertEnum::NO_GRAVITY, $event->getPlace()->getDaedalus());
        if ($gravityAlert !== null) {
            $event->setIsGravity(false);
        }
    }
}
