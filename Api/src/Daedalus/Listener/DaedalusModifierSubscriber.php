<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusModifierSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private ModifierServiceInterface $modifierService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        ModifierServiceInterface $modifierService
    ) {
        $this->daedalusService = $daedalusService;
        $this->modifierService = $modifierService;
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

        if ($change > 0 && ($player = $event->getPlayer())) {
            $change = $this->modifierService->getEventModifiedValue($player, [DaedalusModifierEvent::CHANGE_HULL], ModifierTargetEnum::HULL, $change);
        }

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
