<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Repository\ActionConfigRepositoryInterface;
use Mush\Equipment\DroneTasks\AbstractDroneTask;
use Mush\Equipment\DroneTasks\ExtinguishFireTask;
use Mush\Equipment\DroneTasks\LandTask;
use Mush\Equipment\DroneTasks\RepairBrokenEquipmentTask;
use Mush\Equipment\DroneTasks\ShootHunterTask;
use Mush\Equipment\DroneTasks\TakeoffTask;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

#[ORM\Entity]
class Drone extends GameItem
{
    private const float ATTEMPT_INCREASE = 1.25;
    private const array UPGRADES = [
        EquipmentStatusEnum::TURBO_DRONE_UPGRADE,
        EquipmentStatusEnum::PILOT_DRONE_UPGRADE,
        EquipmentStatusEnum::SENSOR_DRONE_UPGRADE,
        EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE,
    ];

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

    public function cannotApplyTask(AbstractDroneTask $task): bool
    {
        return match ($task->name()) {
            RepairBrokenEquipmentTask::class => $this->cannotRepair(),
            ExtinguishFireTask::class => $this->cannotExtinguish(),
            LandTask::class => $this->cannotLand(),
            ShootHunterTask::class => $this->cannotShootHunter(),
            TakeoffTask::class => $this->cannotTakeoff(),
            default => false,
        };
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

        if ($baseSuccessRate >= 100) {
            return 100;
        }

        return (int) min($baseSuccessRate * self::ATTEMPT_INCREASE ** $this->getFailedRepairAttempts(), 99);
    }

    public function getExtinguishFireSuccessRate(ActionConfigRepositoryInterface $actionConfigRepository): int
    {
        $baseSuccessRate = $actionConfigRepository->findActionSuccessRateByDaedalusAndMechanicOrThrow(
            ActionEnum::EXTINGUISH,
            $this->getDaedalus(),
            EquipmentMechanicEnum::TOOL,
        );

        if ($baseSuccessRate >= 100) {
            return 100;
        }

        return (int) min($baseSuccessRate * self::ATTEMPT_INCREASE ** $this->getExtinguishFailedAttempts(), 99);
    }

    public function getShootHunterSuccessRate(): int
    {
        $baseSuccessRate = $this->shootHunterBaseSuccessRate();

        if ($baseSuccessRate >= 100) {
            return 100;
        }

        $successRate = $baseSuccessRate * $this->pilotBonus();

        return (int) min($successRate * self::ATTEMPT_INCREASE ** $this->getShootHunterFailedAttempts(), 99);
    }

    public function operationalPatrolShipsInRoom(): array
    {
        return $this->getPlace()->getEquipments()
            ->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isAPatrolShip() && $gameEquipment->isOperational())
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

    public function getPilotedPatrolShip(): GameEquipment
    {
        return $this->getPlace()->getFirstEquipmentByMechanicNameOrThrow(EquipmentMechanicEnum::PATROL_SHIP);
    }

    public function getPatrolShipDockingPlace(): Place
    {
        $patrolShip = $this->getPilotedPatrolShip();

        return $this->getDaedalus()->getPlaceByNameOrThrow($patrolShip->getPatrolShipMechanicOrThrow()->getDockingPlace());
    }

    public function isTurbo(): bool
    {
        return $this->hasStatus(EquipmentStatusEnum::TURBO_DRONE_UPGRADE);
    }

    public function isPilot(): bool
    {
        return $this->hasStatus(EquipmentStatusEnum::PILOT_DRONE_UPGRADE);
    }

    public function isSensor(): bool
    {
        return $this->hasStatus(EquipmentStatusEnum::SENSOR_DRONE_UPGRADE);
    }

    public function isFirefighter(): bool
    {
        return $this->hasStatus(EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE);
    }

    /**
     * @return Collection<int, Status>
     */
    public function getUpgrades(): Collection
    {
        return $this->getStatusesByName(self::UPGRADES);
    }

    public function isUpgraded(): bool
    {
        return $this->getUpgrades()->count() > 0;
    }

    public function huntersAreAttacking(): bool
    {
        return $this->getDaedalus()->getAttackingHunters()->count() > 0;
    }

    private function cannotRepair(): bool
    {
        return $this->nothingBrokenInRoom();
    }

    private function cannotExtinguish(): bool
    {
        return $this->isNotFirefighter() || $this->noFireInRoom();
    }

    private function cannotLand(): bool
    {
        return $this->isNotPilot() || $this->isInDaedalus() || $this->huntersAreAttacking() || $this->noLandActionAvailable();
    }

    private function cannotShootHunter(): bool
    {
        return $this->isNotPilot() || $this->isInDaedalus() || $this->noAttackingHunters() || $this->noShootHunterActionAvailable();
    }

    private function cannotTakeoff(): bool
    {
        return $this->isNotPilot() || $this->isInAPatrolShip() || $this->noAttackingHunters() || $this->noPatrolShipTakeoffActionAvailable();
    }

    private function nothingBrokenInRoom(): bool
    {
        return $this->getBrokenDoorsAndEquipmentsInRoom()->isEmpty();
    }

    private function noFireInRoom(): bool
    {
        return $this->getPlace()->doesNotHaveStatus(StatusEnum::FIRE);
    }

    private function isNotFirefighter(): bool
    {
        return $this->doesNotHaveStatus(EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE);
    }

    private function isNotPilot(): bool
    {
        return $this->doesNotHaveStatus(EquipmentStatusEnum::PILOT_DRONE_UPGRADE);
    }

    private function isInDaedalus(): bool
    {
        return $this->getPlace()->getType() === PlaceTypeEnum::ROOM;
    }

    private function noPatrolShipTakeoffActionAvailable(): bool
    {
        $equipment = $this->getPlace()->getFirstEquipmentByMechanicNameOrNull(EquipmentMechanicEnum::PATROL_SHIP);
        if (!$equipment || $equipment->isAMonoplaceShip() === false || $equipment->isNotOperational()) {
            return true;
        }

        return $equipment->hasActionByName(ActionEnum::TAKEOFF) === false;
    }

    private function noAttackingHunters(): bool
    {
        return $this->getDaedalus()->getHuntersAroundDaedalus()->isEmpty();
    }

    private function noShootHunterActionAvailable(): bool
    {
        $patrolShip = $this->getPlace()->getFirstEquipmentByMechanicNameOrNull(EquipmentMechanicEnum::PATROL_SHIP);
        if (!$patrolShip || $patrolShip->isNotOperational()) {
            return true;
        }

        return $patrolShip->hasActionByName(ActionEnum::SHOOT_HUNTER_PATROL_SHIP) === false;
    }

    private function noLandActionAvailable(): bool
    {
        $patrolShip = $this->getPlace()->getFirstEquipmentByMechanicNameOrNull(EquipmentMechanicEnum::PATROL_SHIP);
        if (!$patrolShip || $patrolShip->isNotOperational()) {
            return true;
        }

        return $patrolShip->hasActionByName(ActionEnum::LAND) === false;
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

    private function getShootHunterFailedAttempts(): int
    {
        return $this->getChargeStatusByName(EquipmentStatusEnum::DRONE_SHOOT_HUNTER_FAILED_ATTEMPTS)?->getCharge() ?? 0;
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
