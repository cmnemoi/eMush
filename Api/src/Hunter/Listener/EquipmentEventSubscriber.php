<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Hunter\Repository\HunterRepositoryInterface;
use Mush\Hunter\Repository\HunterTargetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private HunterRepositoryInterface $hunterRepository,
        private HunterTargetRepositoryInterface $hunterTargetRepository,
    ) {}

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
        $hunterTargets = $this->hunterTargetRepository->findAllBy(['patrolShip' => $patrolShip]);

        foreach ($hunterTargets as $hunterTarget) {
            $owner = $this->hunterRepository->findOneByTargetOrThrow($hunterTarget);
            $owner->resetTarget();
            $this->hunterRepository->save($owner);
        }
    }
}
