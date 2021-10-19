<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Modifier\Service\GearModifierServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EquipmentInitEventSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentInitEvent::NEW_EQUIPMENT => 'onNewEquipment'
        ];
    }

    public function onNewEquipment(EquipmentInitEvent $event): void
    {
        $equipmentConfig = $event->getEquipmentConfig();
        $gameEquipment = $event->getGameEquipment();
        $reason = $event->getReason();
        $time = $event->getTime();

        foreach ($equipmentConfig->getInitStatus() as $statusConfig) {
            $this->statusService->createStatusFromConfig($statusConfig, $gameEquipment);
        }
    }
}
