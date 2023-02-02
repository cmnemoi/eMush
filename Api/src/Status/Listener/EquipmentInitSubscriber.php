<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentInitSubscriber implements EventSubscriberInterface
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
            EquipmentInitEvent::NEW_EQUIPMENT => 'onNewEquipment',
        ];
    }

    public function onNewEquipment(EquipmentInitEvent $event): void
    {
        $equipmentConfig = $event->getEquipmentConfig();
        $gameEquipment = $event->getGameEquipment();
        $reasons = $event->getTags();
        $time = $event->getTime();

        foreach ($equipmentConfig->getInitStatuses() as $statusConfig) {
            $this->statusService->createStatusFromConfig(
                $statusConfig,
                $gameEquipment,
                $reasons,
                $time
            );
        }
    }
}
