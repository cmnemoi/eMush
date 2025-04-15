<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\DoorEnum;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class DaedalusIncidentService implements DaedalusIncidentServiceInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
        private GameEquipmentRepositoryInterface $gameEquipmentRepository,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        private RandomServiceInterface $randomService,
        private StatusServiceInterface $statusService,
    ) {}

    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var Place $roomToFire */
        $roomToFire = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getRoomsWithoutFire()->toArray(),
            number: 1
        )->first();
        if (!$roomToFire) {
            return;
        }

        $this->statusService->createStatusFromName(
            StatusEnum::FIRE,
            $roomToFire,
            [EventEnum::NEW_CYCLE, StatusEnum::FIRE],
            $date
        );
    }

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var Place $roomToShake */
        $roomToShake = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getRoomsWithAlivePlayers()->toArray(),
            number: 1
        )->first();
        if (!$roomToShake) {
            return;
        }

        $roomEvent = new RoomEvent(
            $roomToShake,
            [EventEnum::NEW_CYCLE, RoomEvent::TREMOR],
            $date
        );
        $this->eventService->callEvent($roomEvent, RoomEvent::TREMOR);
    }

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var Place $roomToElectrize */
        $roomToElectrize = $this->getRandomElementsFromArray->execute(elements: $daedalus->getRooms()->toArray(), number: 1)->first();
        if (!$roomToElectrize) {
            return;
        }

        $roomEvent = new RoomEvent(
            $roomToElectrize,
            [EventEnum::NEW_CYCLE, RoomEvent::ELECTRIC_ARC],
            $date
        );
        $this->eventService->callEvent($roomEvent, RoomEvent::ELECTRIC_ARC);
    }

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): void
    {
        $workingEquipmentBreakRateDistribution = $this->getWorkingEquipmentBreakRateDistribution($daedalus);

        // If there is no working equipment, we don't have to break anything
        if ($workingEquipmentBreakRateDistribution->isEmpty()) {
            return;
        }

        // If there is less working equipment than the number of equipment we want to break
        // we break the number of working equipment instead to avoid an error
        $numberOfEquipmentBroken = min(1, $workingEquipmentBreakRateDistribution->count());
        $equipmentToBreak = $this
            ->randomService
            ->getRandomDaedalusEquipmentFromProbaCollection(
                $workingEquipmentBreakRateDistribution,
                $numberOfEquipmentBroken,
                $daedalus
            );
        if (empty($equipmentToBreak)) {
            return;
        }

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $equipmentToBreak[0],
            [EventEnum::NEW_CYCLE, EquipmentEvent::EQUIPMENT_BROKEN],
            $date
        );
    }

    public function handleDoorBreak(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var Door $doorToBreak */
        $doorToBreak = $this->getRandomElementsFromArray->execute(
            elements: $this->getBreakableDoors($daedalus),
            number: 1
        )->first();
        if (!$doorToBreak) {
            return;
        }

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $doorToBreak,
            [EventEnum::NEW_CYCLE, EquipmentEvent::DOOR_BROKEN],
            $date
        );
    }

    public function handlePanicCrisis(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var Player $playerToPanic */
        $playerToPanic = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getAlivePlayers()->getHumanPlayer()->toArray(),
            number: 1
        )->first();
        if (!$playerToPanic) {
            return;
        }

        $playerEvent = new PlayerEvent(
            $playerToPanic,
            [EventEnum::NEW_CYCLE, PlayerEvent::PANIC_CRISIS],
            $date
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::PANIC_CRISIS);
    }

    public function handleMetalPlates(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var Player $playerToSteelPlate */
        $playerToSteelPlate = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getPlayers()->getPlayerAliveAndInRoom()->toArray(),
            number: 1
        )->first();
        if (!$playerToSteelPlate) {
            return;
        }

        $playerEvent = new PlayerEvent(
            $playerToSteelPlate,
            [EventEnum::NEW_CYCLE, PlayerEvent::METAL_PLATE],
            $date
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::METAL_PLATE);
    }

    public function handleCrewDisease(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var Player $playerToMakeSick */
        $playerToMakeSick = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getAlivePlayers()->toArray(),
            number: 1
        )->first();
        if (!$playerToMakeSick) {
            return;
        }

        $playerEvent = new PlayerEvent(
            $playerToMakeSick,
            [EventEnum::NEW_CYCLE, PlayerEvent::CYCLE_DISEASE],
            $date
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::CYCLE_DISEASE);
    }

    public function handleOxygenTankBreak(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var GameEquipment $oxygenTankToBreak */
        $oxygenTankToBreak = $this->getRandomElementsFromArray->execute(
            elements: $this->getWorkingOxygenTanks($daedalus),
            number: 1
        )->first();
        if (!$oxygenTankToBreak) {
            return;
        }

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $oxygenTankToBreak,
            [EventEnum::NEW_CYCLE, EquipmentEvent::EQUIPMENT_BROKEN],
            $date
        );
    }

    public function handleFuelTankBreak(Daedalus $daedalus, \DateTime $date): void
    {
        /** @var GameEquipment $fuelTankToBreak */
        $fuelTankToBreak = $this->getRandomElementsFromArray->execute(
            elements: $this->getWorkingFuelTanks($daedalus),
            number: 1
        )->first();
        if (!$fuelTankToBreak) {
            return;
        }

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $fuelTankToBreak,
            [EventEnum::NEW_CYCLE, EquipmentEvent::EQUIPMENT_BROKEN],
            $date
        );
    }

    public function getBreakableDoors(Daedalus $daedalus): array
    {
        $criteria = new GameEquipmentCriteria($daedalus);
        $criteria->setInstanceOf([Door::class]);

        $daedalusDoors = $this->gameEquipmentRepository->findByCriteria($criteria);

        $daedalusDoorsNames = array_map(static fn (Door $door) => $door->getName(), $daedalusDoors);

        $breakableDoorsNames = array_filter($daedalusDoorsNames, static fn (string $doorName) => DoorEnum::isBreakable($doorName));

        $breakableDoors = array_filter($daedalusDoors, static fn (Door $door) => \in_array($door->getName(), $breakableDoorsNames, true));

        $breakableDoors = array_filter($breakableDoors, static fn (Door $door) => !$door->isBroken());

        return $breakableDoors;
    }

    public function getWorkingOxygenTanks(Daedalus $daedalus): array
    {
        $tanks = $this->gameEquipmentRepository->findByNameAndDaedalus(EquipmentEnum::OXYGEN_TANK, $daedalus);

        return array_filter($tanks, static fn (GameEquipment $tank) => !$tank->isBroken());
    }

    public function getWorkingFuelTanks(Daedalus $daedalus): array
    {
        $tanks = $this->gameEquipmentRepository->findByNameAndDaedalus(EquipmentEnum::FUEL_TANK, $daedalus);

        return array_filter($tanks, static fn (GameEquipment $tank) => !$tank->isBroken());
    }

    public function getWorkingEquipmentDistribution(Daedalus $daedalus): ProbaCollection
    {
        return $this->getWorkingEquipmentBreakRateDistribution($daedalus);
    }

    /**
     * This function returns the distribution of the working equipment break rate
     * to avoid trying to break a piece of equipment that is already broken
     * (and get less broken equipment than expected).
     */
    private function getWorkingEquipmentBreakRateDistribution(Daedalus $daedalus): ProbaCollection
    {
        $equipmentBreakRateDistribution = $daedalus
            ->getGameConfig()
            ->getDifficultyConfig()
            ->getEquipmentBreakRateDistribution();

        $absentEquipments = [];

        /** @var string $equipmentName */
        foreach ($equipmentBreakRateDistribution as $equipmentName => $probability) {
            /** @var array<int, GameEquipment> $equipments */
            $equipments = $this->gameEquipmentRepository->findByNameAndDaedalus($equipmentName, $daedalus);
            // first, remove equipment which not present on the daedalus
            if (empty($equipments)) {
                $absentEquipments[] = $equipmentName;

                continue;
            }

            // then, remove equipment which is already broken or patrol ships in space battle
            foreach ($equipments as $equipment) {
                if ($equipment->isBroken() || $equipment->isInSpaceBattle()) {
                    $absentEquipments[] = $equipmentName;
                }
            }
        }

        return $equipmentBreakRateDistribution->withdrawElements($absentEquipments);
    }
}
