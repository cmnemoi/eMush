<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Modifier\Service\GearModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentInitSubscriber implements EventSubscriberInterface
{
    private GearModifierServiceInterface $gearModifierService;

    public function __construct(
        GearModifierServiceInterface $gearModifierService,
    ) {
        $this->gearModifierService = $gearModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentInitEvent::NEW_EQUIPMENT => 'onNewEquipment'
        ];
    }

    public function onNewEquipment(EquipmentInitEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        $this->gearModifierService->gearCreated($equipment);
    }
}
