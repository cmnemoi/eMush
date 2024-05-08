<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

#[ORM\Entity]
class Drone extends GameItem
{
    private const float ATTEMPT_INCREASE = 1.25;

    #[ORM\OneToOne(mappedBy: 'drone', targetEntity: DroneInfo::class)]
    private DroneInfo $droneInfo;

    public function __construct(
        EquipmentHolderInterface $equipmentHolder,
    ) {
        parent::__construct($equipmentHolder);
    }

    public function getDroneInfo(): DroneInfo
    {
        return $this->droneInfo;
    }

    public function setDroneInfo(DroneInfo $droneInfo): void
    {
        $this->droneInfo = $droneInfo;
    }

    public function getNickname(): int
    {
        return $this->droneInfo->getNickname();
    }

    public function getSerialNumber(): int
    {
        return $this->droneInfo->getSerialNumber();
    }

    /**
     * @return ArrayCollection<int, Place>
     */
    public function getAdjacentRooms(): ArrayCollection
    {
        return $this->getPlace()->getAdjacentRooms();
    }

    /**
     * @return Collection<int, GameEquipment>
     */
    public function getBrokenEquipmentsInRoom(): Collection
    {
        return $this->getPlace()->getBrokenEquipments();
    }

    public function getChargesStatus(): ChargeStatus
    {
        return $this->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::DRONE;
    }

    public function getRepairSuccessRateForEquipment(GameEquipment $gameEquipment): int
    {
        $baseSuccessRate = $gameEquipment->getActionByNameOrThrow(ActionEnum::REPAIR)->getSuccessRate();

        return (int) ($baseSuccessRate * self::ATTEMPT_INCREASE ** $this->getFailedRepairAttempts());
    }

    private function getFailedRepairAttempts(): int
    {
        return $this->getChargeStatusByName(StatusEnum::ATTEMPT)?->getCharge() ?? 0;
    }
}
