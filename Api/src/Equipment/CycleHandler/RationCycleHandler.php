<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RationCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::RATION;

    private EquipmentFactoryInterface $gameEquipmentService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EquipmentFactoryInterface $gameEquipmentService,
        EventDispatcherInterface  $eventDispatcher,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleNewCycle($object, \DateTime $dateTime): void
    {
    }

    public function handleNewDay($object, \DateTime $dateTime): void
    {
        $gameRation = $object;

        if (!$gameRation instanceof Equipment) {
            return;
        }

        /** @var Ration $rationType */
        $rationType = $gameRation->getConfig()->getMechanicByName(EquipmentMechanicEnum::RATION);

        if (null === $rationType) {
            return;
        }

        // @TODO destroy perishable item according to NERON BIOS
        $this->handleStatus($gameRation, $rationType);
    }

    private function handleStatus(Equipment $gameRation, Ration $ration): void
    {
        // If ration is not perishable or frozen oe decomposing do nothing
        if (!$ration->isPerishable() ||
            $gameRation->getStatuses()->exists(
                fn (int $key, Status $status) => in_array($status->getName(), [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::FROZEN])
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
