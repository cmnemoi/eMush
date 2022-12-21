<?php

namespace Mush\Equipment\Listener;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusEventSubscriber implements EventSubscriberInterface
{
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        EquipmentEffectServiceInterface $equipmentEffectService,
    ) {
        $this->equipmentEffectService = $equipmentEffectService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusEvent::DELETE_DAEDALUS => ['onDeleteDaedalus', 1000],
        ];
    }

    public function onDeleteDaedalus(DaedalusEvent $event): void
    {
        $this->equipmentEffectService->removeAllEffects($event->getDaedalus());
    }
}
