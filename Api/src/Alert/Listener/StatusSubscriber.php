<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService,
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_DELETED => 'onStatusDeleted',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        match ($event->getStatusName()) {
            EquipmentStatusEnum::BROKEN => $this->alertService->handleEquipmentBreak($event->getGameEquipmentStatusHolder()),
            StatusEnum::FIRE => $this->alertService->handleFireStart($event->getPlaceOrThrow()),
            DaedalusStatusEnum::NO_GRAVITY => $this->alertService->gravityAlert($event->getDaedalus(), AlertEnum::BREAK),
            DaedalusStatusEnum::NO_GRAVITY_REPAIRED => $this->alertService->gravityAlert($event->getDaedalus(), AlertEnum::REPAIR),
            PlayerStatusEnum::LOST => $this->alertService->handlePlayerLost($event->getDaedalus()),
            PlayerStatusEnum::PARIAH => $this->alertService->handlePariahApplied($event->getPlayerStatusHolder()),
            default => null,
        };
    }

    public function onStatusDeleted(StatusEvent $event): void
    {
        if ($event->getStatusName() === PlayerStatusEnum::LOST) {
            $this->alertService->handleLostPlayerFound($event->getDaedalus());
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        // If Daedalus is being deleted, alerts have already been deleted
        if ($event->hasTag(DaedalusEvent::DELETE_DAEDALUS)) {
            return;
        }

        match ($event->getStatusName()) {
            EquipmentStatusEnum::BROKEN => $this->alertService->handleEquipmentRepair($event->getGameEquipmentStatusHolder()),
            StatusEnum::FIRE => $this->alertService->handleFireStop($event->getPlaceOrThrow()),
            DaedalusStatusEnum::NO_GRAVITY_REPAIRED => $this->alertService->gravityAlert($event->getDaedalus(), AlertEnum::GRAVITY_REBOOT),
            PlayerStatusEnum::PARIAH => $this->alertService->handlePariahRemoved($event->getPlayerStatusHolder()),
            default => null,
        };
    }
}
