<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Repository\ActionConfigRepositoryInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\NPCTasks\Drone\AbstractDroneTask;
use Mush\Equipment\NPCTasks\Drone\ExtinguishFireTask;
use Mush\Equipment\NPCTasks\Drone\LandTask;
use Mush\Equipment\NPCTasks\Drone\RepairBrokenEquipmentTask;
use Mush\Equipment\NPCTasks\Drone\ShootHunterTask;
use Mush\Equipment\NPCTasks\Drone\TakeoffTask;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\TranslationServiceInterface as Translate;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

#[ORM\Entity]
class Drone extends Npc
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

        return (int) min($baseSuccessRate * self::ATTEMPT_INCREASE ** (float) $this->getFailedRepairAttempts(), 99);
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
            ->filter(
                static fn (GameEquipment $gameEquipment) => (
                    $gameEquipment instanceof SpaceShip
                    && $gameEquipment->getName() === EquipmentEnum::PATROL_SHIP
                    && $gameEquipment->isOperational()
                )
            )
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
        $patrolShip = $this->getPlace()->getFirstPatrolShipOrThrow();

        return $patrolShip->getWeaponMechanicOrThrow()->getBaseDamageRange();
    }

    public function getPilotedPatrolShip(): SpaceShip
    {
        // @var SpaceShip $patrolShip
        return $this->getPlace()->getFirstPatrolShipOrThrow();
    }

    public function getPatrolShipDockingPlace(): Place
    {
        /** @var SpaceShip $patrolShip */
        $patrolShip = $this->getPilotedPatrolShip();

        return $this->getDaedalus()->getPlaceByNameOrThrow($patrolShip->getDockingPlace());
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

    /**
     * @psalm-suppress InvalidFunctionCall
     */
    public function toExamineLogParameters(Translate $translate): array
    {
        if (!$this->isUpgraded()) {
            return ['drone_upgrades' => ''];
        }

        $upgrades = $this->getUpgrades()->map(function (Status $upgrade) use ($translate) {
            return $translate(
                $upgrade->getName() . '.description',
                [],
                'status',
                $this->getDaedalus()->getLanguage()
            );
        })->toArray();

        return ['drone_upgrades' => implode('//', $upgrades)];
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
        return $this->isNotPilot() || !$this->isInAPatrolShip() || $this->noLandActionAvailable();
    }

    private function cannotShootHunter(): bool
    {
        return $this->isNotPilot() || !$this->isInAPatrolShip() || $this->noAttackingHunters() || $this->noShootHunterActionAvailable();
    }

    private function cannotTakeoff(): bool
    {
        return $this->isNotPilot() || !$this->isInDaedalus() || $this->noAttackingHunters() || $this->noPatrolShipTakeoffActionAvailable();
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
        $patrolShips = $this->getPlace()->getEquipments()->filter(
            static fn (GameEquipment $gameEquipment) => (
                $gameEquipment instanceof SpaceShip
                && $gameEquipment->getName() === EquipmentEnum::PATROL_SHIP
                && $gameEquipment->isOperational()
                && $gameEquipment->hasActionByName(ActionEnum::TAKEOFF)
            )
        );

        return $patrolShips->isEmpty();
    }

    private function noAttackingHunters(): bool
    {
        return $this->getDaedalus()->getAttackingHunters()->isEmpty();
    }

    private function noShootHunterActionAvailable(): bool
    {
        $patrolShip = $this->getPlace()->getFirstPatrolShipOrThrow();
        if ($patrolShip->isNotOperational()) {
            return true;
        }

        return $patrolShip->hasActionByName(ActionEnum::SHOOT_HUNTER_PATROL_SHIP) === false;
    }

    private function noLandActionAvailable(): bool
    {
        $patrolShip = $this->getPlace()->getFirstPatrolShipOrThrow();
        if ($patrolShip->isBroken()) {
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
        $patrolShip = $this->getPlace()->getFirstPatrolShipOrThrow();

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
