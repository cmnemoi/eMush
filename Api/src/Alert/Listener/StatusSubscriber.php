<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                if (!$holder instanceof GameEquipment) {
                    throw new UnexpectedTypeException($holder, GameEquipment::class);
                }
                $this->alertService->handleEquipmentBreak($holder);

                if ($holder->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
                    $this->alertService->gravityAlert($holder->getDaedalus(), true);
                }

                return;

            case StatusEnum::FIRE:
                $this->alertService->handleFireStart($event->getPlace());

                return;
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                if (!$holder instanceof GameEquipment) {
                    throw new UnexpectedTypeException($holder, GameEquipment::class);
                }
                $this->alertService->handleEquipmentRepair($holder);

                if ($holder->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
                    $this->alertService->gravityAlert($holder->getDaedalus(), false);
                }

                return;

            case StatusEnum::FIRE:
                $this->alertService->handleFireStop($event->getPlace());

                return;
        }
    }
}
