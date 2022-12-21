<?php

namespace Mush\Alert\Listener;

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
}
