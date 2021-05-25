<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Event\EquipmentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService
    ) {
        $this->neronMessageService = $neronMessageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_BROKEN => 'onBrokenEquipment',
        ];
    }

    public function onBrokenEquipment(EquipmentEvent $event): void
    {
        $this->neronMessageService->createBrokenEquipmentMessage($event->getEquipment(), $event->getVisibility(), $event->getTime());
    }
}
