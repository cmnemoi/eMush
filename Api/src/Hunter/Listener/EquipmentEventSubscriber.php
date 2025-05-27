<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Hunter\Repository\HunterTargetRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private HunterTargetRepository $hunterTargetRepository) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_DESTROYED => ['onEquipmentDestroyed'],
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        if ($event instanceof InteractWithEquipmentEvent && $event->getGameEquipment()->isInAPatrolShip()) {
            $this->deleteHunterTarget($event);
        }
    }

    private function deleteHunterTarget(InteractWithEquipmentEvent $event): void
    {
        $patrolShip = $event->getGameEquipment();
        $hunterTargets = $this->hunterTargetRepository->findAllByPatrolShip($patrolShip);

        foreach ($hunterTargets as $hunterTarget) {
            $hunterTarget->reset();
        }
    }
}
