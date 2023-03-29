<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertService;
use Mush\Hunter\Event\HunterPoolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterSubscriber implements EventSubscriberInterface
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
            HunterPoolEvent::UNPOOL_HUNTERS => ['onUnpoolHunters', -10],
        ];
    }

    public function onUnpoolHunters(HunterPoolEvent $event): void
    {
        $this->alertService->handleHunterArrival($event->getDaedalus());
    }
}
