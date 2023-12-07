<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertService;
use Mush\Daedalus\Event\DaedalusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
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
            DaedalusEvent::DELETE_DAEDALUS => ['onDeleteDaedalus', 1000],
            DaedalusEvent::TRAVEL_LAUNCHED => ['onTravelLaunched', -1], // do this slightly after putting hunters in pool
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $alerts = $this->alertService->findByDaedalus($daedalus);

        foreach ($alerts as $alert) {
            $this->alertService->delete($alert);
        }
    }

    public function onTravelLaunched(DaedalusEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $hunterAlert = $this->alertService->findByNameAndDaedalus(AlertEnum::HUNTER, $daedalus);

        if ($hunterAlert !== null && $daedalus->getAttackingHunters()->isEmpty()) {
            $this->alertService->delete($hunterAlert);
        }
    }
}
