<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;

class RationCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::RATION;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventServiceInterface $eventService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EventServiceInterface $eventService,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventService = $eventService;
    }

    public function handleNewCycle($object, \DateTime $dateTime): void
    {
    }

    public function handleNewDay($object, \DateTime $dateTime): void
    {
        if (!($object instanceof GameEquipment)) {
            return;
        }

        $gameRation = $object;

        /** @var Ration $rationType */
        $rationType = $gameRation->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (null === $rationType) {
            return;
        }

        // @TODO destroy perishable item according to NERON BIOS
        $this->handleStatus($gameRation, $rationType);

        $this->gameEquipmentService->persist($gameRation);
    }

    private function handleStatus(GameEquipment $gameRation, Ration $ration): void
    {
        // If ration is not perishable or frozen or decomposing do nothing
        if (!$ration->getIsPerishable()
            || $gameRation->getStatuses()->exists(
                fn (int $key, Status $status) => in_array($status->getName(), [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::FROZEN])
            )
        ) {
            return;
        }

        if ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::UNSTABLE)) {
            $removeStatusEvent = new StatusEvent(
                statusName: $currentStatus->getName(),
                holder: $gameRation,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            );
            $this->eventService->callEvent($removeStatusEvent, StatusEvent::STATUS_REMOVED);
            $nextStatus = EquipmentStatusEnum::HAZARDOUS;
        } elseif ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::HAZARDOUS)) {
            $removeStatusEvent = new StatusEvent(
                statusName: $currentStatus->getName(),
                holder: $gameRation,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            );
            $this->eventService->callEvent($removeStatusEvent, StatusEvent::STATUS_REMOVED);
            $nextStatus = EquipmentStatusEnum::DECOMPOSING;
        } else {
            $nextStatus = EquipmentStatusEnum::UNSTABLE;
        }

        $statusEvent = new StatusEvent($nextStatus, $gameRation, [EventEnum::NEW_DAY], new \DateTime());
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
