<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RationCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::RATION;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
    }

    public function handleNewCycle($object, \DateTime $dateTime): void {}

    public function handleNewDay($object, \DateTime $dateTime): void
    {
        if (!$object instanceof GameEquipment) {
            return;
        }

        /** @var Ration $rationType */
        $rationType = $object->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);
        if ($rationType === null) {
            return;
        }

        // @TODO destroy perishable item according to NERON BIOS
        $this->handleStatus($object, $rationType);
        $this->gameEquipmentService->persist($object);
    }

    private function handleStatus(GameEquipment $gameRation, Ration $ration): void
    {
        // If ration is not perishable or frozen or decomposing or not in a room do nothing
        if (!$ration->getIsPerishable()
            || $gameRation->getStatuses()->exists(
                static fn (int $key, Status $status) => \in_array($status->getName(), [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::FROZEN], true)
            )
            || ($gameRation->getHolder() instanceof Place && $gameRation->getPlace()->getType() !== PlaceTypeEnum::ROOM)
        ) {
            return;
        }

        if ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::UNSTABLE)) {
            $this->statusService->removeStatus(
                statusName: $currentStatus->getName(),
                holder: $gameRation,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            );
            $nextStatus = EquipmentStatusEnum::HAZARDOUS;
        } elseif ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::HAZARDOUS)) {
            $this->statusService->removeStatus(
                statusName: $currentStatus->getName(),
                holder: $gameRation,
                tags: [EventEnum::NEW_DAY],
                time: new \DateTime()
            );
            $nextStatus = EquipmentStatusEnum::DECOMPOSING;
        } else {
            $nextStatus = EquipmentStatusEnum::UNSTABLE;
        }

        $this->statusService->createStatusFromName($nextStatus, $gameRation, [EventEnum::NEW_DAY], new \DateTime());
    }
}
