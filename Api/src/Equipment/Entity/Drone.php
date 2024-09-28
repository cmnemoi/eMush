<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Repository\ActionConfigRepositoryInterface;
use Mush\Equipment\DroneTasks\AbstractDroneTask;
use Mush\Equipment\DroneTasks\ShootHunterTask;
use Mush\Equipment\DroneTasks\TakeoffTask;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

#[ORM\Entity]
class Drone extends GameItem
{
    private const float ATTEMPT_INCREASE = 1.25;

    #[ORM\OneToOne(mappedBy: 'drone', targetEntity: DroneInfo::class, cascade: ['remove'])]
    private DroneInfo $droneInfo;

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
     * @return Collection<int, Place>
     */
    public function getAdjacentRooms(): Collection
    {
        return $this->getPlace()->getAdjacentRooms();
    }

    /**
     * @return Collection<int, GameEquipment>
     */
    public function getBrokenDoorsAndEquipmentsInRoom(): Collection
    {
        return $this->getPlace()->getBrokenDoorsAndEquipments();
    }

    public function getChargeStatus(): ChargeStatus
    {
        return $this->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
    }

    public function getNumberOfActions(): int
    {
        return $this->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::DRONE;
    }

    public function getRepairSuccessRateForEquipment(GameEquipment $gameEquipment): int
    {
        $repairActionConfig = $gameEquipment->getActionConfigByNameOrNull(ActionEnum::REPAIR) ?? $gameEquipment->getActionConfigByNameOrThrow(ActionEnum::RENOVATE);

        $baseSuccessRate = $repairActionConfig->getSuccessRate();

        return (int) ($baseSuccessRate * self::ATTEMPT_INCREASE ** $this->getFailedRepairAttempts());
    }

    public function getExtinguishFireSuccessRate(ActionConfigRepositoryInterface $actionConfigRepository): int
    {
        $baseSuccessRate = $actionConfigRepository->findActionSuccessRateByDaedalusAndMechanicOrThrow(
            ActionEnum::EXTINGUISH,
            $this->getDaedalus(),
            EquipmentMechanicEnum::TOOL,
        );

        return (int) ($baseSuccessRate * self::ATTEMPT_INCREASE ** $this->getExtinguishFailedAttempts());
    }

    public function getShootHunterSuccessRate(): int
    {
        return (int) ($this->shootHunterBaseSuccessRate() * $this->pilotBonus());
    }

    public function cannotApplyTask(AbstractDroneTask $task): bool
    {
        return match ($task->name()) {
            TakeoffTask::class => $this->isNotPilot() || $this->noAttackingHunters() || $this->noOperationalPatrolShipInRoom(),
            ShootHunterTask::class => $this->isNotPilot() || $this->noAttackingHunters() || $this->noShootHunterActionAvailable(),
            default => false,
        };
    }

    public function noFireInRoom(): bool
    {
        return $this->getPlace()->doesNotHaveStatus(StatusEnum::FIRE);
    }

    public function isNotFirefighter(): bool
    {
        return $this->doesNotHaveStatus(EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE);
    }

    public function operationalPatrolShipsInRoom(): array
    {
        return $this->getPlace()->getEquipments()
            ->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isOperational() && $gameEquipment->isAPatrolShip())
            ->toArray();
    }

    public function turboWorked(): bool
    {
        return $this->numberOfActions() > 1;
    }

    public function turboChance(): int
    {
        return $this->getChargeStatusByName(EquipmentStatusEnum::TURBO_DRONE_UPGRADE)?->getCharge() ?? 0;
    }

    public function shootHunterDamageRange(): ProbaCollection
    {
        $patrolShip = $this->getPlace()->getFirstEquipmentByMechanicNameOrThrow(EquipmentMechanicEnum::PATROL_SHIP);

        return $patrolShip->getWeaponMechanicOrThrow()->getBaseDamageRange();
    }

    private function isNotPilot(): bool
    {
        return $this->doesNotHaveStatus(EquipmentStatusEnum::PILOT_DRONE_UPGRADE);
    }

    private function noOperationalPatrolShipInRoom(): bool
    {
        return empty($this->operationalPatrolShipsInRoom());
    }

    private function noAttackingHunters(): bool
    {
        return $this->getDaedalus()->getAttackingHunters()->isEmpty();
    }

    private function noShootHunterActionAvailable(): bool
    {
        $patrolShip = $this->getPlace()->getFirstEquipmentByMechanicNameOrNull(EquipmentMechanicEnum::PATROL_SHIP);
        if (!$patrolShip || $patrolShip->isNotOperational()) {
            return true;
        }

        return $patrolShip->hasActionByName(ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP) === false;
    }

    private function numberOfActions(): int
    {
        return $this->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge();
    }

    private function getFailedRepairAttempts(): int
    {
        return $this->getChargeStatusByName(EquipmentStatusEnum::DRONE_REPAIR_FAILED_ATTEMPTS)?->getCharge() ?? 0;
    }

    private function getExtinguishFailedAttempts(): int
    {
        return $this->getChargeStatusByName(EquipmentStatusEnum::DRONE_EXTINGUISH_FAILED_ATTEMPTS)?->getCharge() ?? 0;
    }

    private function shootHunterBaseSuccessRate(): int
    {
        $patrolShip = $this->getPlace()->getFirstEquipmentByMechanicNameOrThrow(EquipmentMechanicEnum::PATROL_SHIP);

        return $patrolShip->getWeaponMechanicOrThrow()->getBaseAccuracy();
    }

    private function pilotBonus(): float
    {
        return $this
            ->getModifiers()
            ->getByModifierNameOrThrow(ModifierNameEnum::PILOT_DRONE_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getDelta();
    }
}
