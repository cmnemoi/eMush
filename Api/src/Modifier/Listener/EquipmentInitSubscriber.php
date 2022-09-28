<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Modifier\Service\EquipmentModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentInitSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $gearModifierService;

    public function __construct(
        EquipmentModifierServiceInterface $gearModifierService,
    ) {
        $this->gearModifierService = $gearModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentInitEvent::NEW_EQUIPMENT => 'onNewEquipment',
        ];
    }

    public function onNewEquipment(EquipmentInitEvent $event): void
    {
        codecept_debug('oui');
        $equipment = $event->getGameEquipment();
        $this->gearModifierService->gearCreated($equipment);
    }
}
