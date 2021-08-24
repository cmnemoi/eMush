<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusModifierSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        DaedalusServiceInterface $daedalusService
    ) {
        $this->daedalusService = $daedalusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusModifierEvent::CHANGE_HULL => 'onChangeHull',
            DaedalusModifierEvent::CHANGE_OXYGEN => 'onChangeOxygen',
            DaedalusModifierEvent::CHANGE_FUEL => 'onChangeFuel',
        ];
    }

    public function onChangeHull(DaedalusModifierEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $date = $event->getTime();
        $change = $event->getQuantity();

        $this->daedalusService->changeHull($daedalus, $change, $date);
    }

    public function onChangeOxygen(DaedalusModifierEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $change = $event->getQuantity();

        $this->daedalusService->changeOxygenLevel($daedalus, $change);
    }

    public function onChangeFuel(DaedalusModifierEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $change = $event->getQuantity();

        $this->daedalusService->changeFuelLevel($daedalus, $change);
    }
}
