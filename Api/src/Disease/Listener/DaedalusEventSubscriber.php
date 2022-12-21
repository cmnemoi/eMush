<?php

namespace Mush\Disease\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    public function __construct(
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
    ) {
        $this->consumableDiseaseService = $consumableDiseaseService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => ['onDeleteDaedalus', 1000],
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        $this->consumableDiseaseService->removeAllConsumableDisease($event->getDaedalus());
    }
}
