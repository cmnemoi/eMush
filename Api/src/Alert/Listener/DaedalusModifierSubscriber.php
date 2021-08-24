<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusModifierSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusModifierEvent::CHANGE_HULL => 'onChangeHull',
            DaedalusModifierEvent::CHANGE_OXYGEN => 'onChangeOxygen',
        ];
    }

    public function onChangeHull(DaedalusModifierEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $change = $event->getQuantity();

        $this->alertService->hullAlert($daedalus, $change);
    }

    public function onChangeOxygen(DaedalusModifierEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $change = $event->getQuantity();

        $this->alertService->oxygenAlert($daedalus, $change);
    }
}
