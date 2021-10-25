<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Service\GearModifierService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private GearModifierService $gearModifierService;

    public function __construct(
        GearModifierService $gearModifierService
    ) {
        $this->gearModifierService = $gearModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$holder instanceof GameEquipment) {
                throw new UnexpectedTypeException($holder, GameEquipment::class);
            }
            $this->gearModifierService->gearDestroyed($holder);
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$holder instanceof GameEquipment) {
                throw new UnexpectedTypeException($holder, GameEquipment::class);
            }
            $this->gearModifierService->gearCreated($holder);
        }
    }
}
