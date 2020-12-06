<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
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

    public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime)
    {
    }

    public function handleNewDay($gameRation, $daedalus, \DateTime $dateTime)
    {
        if (!$gameRation instanceof GameEquipment) {
            return;
        }
        $rationType = $gameRation->getEquipment()->getRationsMechanic();

        if (null === $rationType || !$rationType instanceof Ration) {
            return;
        }

        //@TODO destroy perishable item accroding to NERON BIOS
        $this->handleStatus($gameRation, $rationType);

        $this->gameEquipmentService->persist($gameRation);
    }

    private function handleStatus(GameEquipment $gameRation, Ration $ration)
    {
        //If ration is not perishable or frozen oe decomposing do nothing
        if (!$ration->isPerishable() ||
            $gameRation->getStatuses()->exists(
                fn (int $key, Status $status) => (
                in_array($status->getName(), [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::FROZEN]))
            )
        ) {
            return;
        }

        if ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::UNSTABLE)) {
            $gameRation->removeStatus($currentStatus);
            $nextStatus = EquipmentStatusEnum::HAZARDOUS;
        } elseif ($currentStatus = $gameRation->getStatusByName(EquipmentStatusEnum::HAZARDOUS)) {
            $gameRation->removeStatus($currentStatus);
            $nextStatus = EquipmentStatusEnum::DECOMPOSING;
        } else {
            $nextStatus = EquipmentStatusEnum::UNSTABLE;
        }

        $status = $this->statusService->createCoreEquipmentStatus($nextStatus, $gameRation, VisibilityEnum::HIDDEN);
        $gameRation->addStatus($status);
    }
}
