<?php

namespace Mush\Situation\Listener;

use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Situation\Service\SituationServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusModifierSubscriber implements EventSubscriberInterface
{
    private SituationServiceInterface $situationService;

    public function __construct(
        SituationServiceInterface $situationService
    ) {
        $this->situationService = $situationService;
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
        $date = $event->getTime();

        $change = $event->getQuantity();
        if ($change === null) {
            throw new \LogicException('quantity should be provided');
        }

        $this->situationService->hullSituation($daedalus, $change);
    }

    public function onChangeOxygen(DaedalusModifierEvent $event): void
    {
        $daedalus = $event->getDaedalus();

        $change = $event->getQuantity();
        if ($change === null) {
            throw new \LogicException('quantity should be provided');
        }

        $this->situationService->oxygenSituation($daedalus, $change);
    }
}
