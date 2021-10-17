<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Entity\Config\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RationCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::RATION;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime): void
    {
    }

    public function handleNewDay($object, $daedalus, \DateTime $dateTime): void
    {
        $gameRation = $object;

        if (!$gameRation instanceof GameEquipment) {
            return;
        }

        $rationType = $gameRation->getEquipment()->getRationsMechanic();

        if (null === $rationType) {
            return;
        }

        //@TODO destroy perishable item accroding to NERON BIOS
        $this->handleStatus($gameRation, $rationType);

        $this->gameEquipmentService->persist($gameRation);
    }

    private function handleStatus(GameEquipment $gameRation, Ration $ration): void
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

        $statusEvent = new StatusEvent($nextStatus, $gameRation, EventEnum::NEW_DAY, new \DateTime());
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }
}
