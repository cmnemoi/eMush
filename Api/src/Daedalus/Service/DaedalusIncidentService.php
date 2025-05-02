<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Event\RoomEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
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

    public function handleFireEvents(array $rooms, \DateTime $date): void
    {
        /** @var Place $roomToFire */
        $roomToFire = $this->getRandomElementsFromArray->execute(
            elements: $rooms,
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

    public function handleTremorEvents(array $rooms, \DateTime $date): void
    {
        /** @var Place $roomToShake */
        $roomToShake = $this->getRandomElementsFromArray->execute(
            elements: $rooms,
            number: 1
        )->first();
        if (!$roomToShake) {
            return;
        }

        $this->statusService->createStatusFromName(
            PlaceStatusEnum::SELECTED_FOR_JOLT->toString(),
            $roomToShake,
            [EventEnum::NEW_CYCLE, RoomEvent::TREMOR],
            $date
        );

        $roomEvent = new RoomEvent(
            $roomToShake,
            [EventEnum::NEW_CYCLE, RoomEvent::TREMOR],
            $date
        );
        $this->eventService->callEvent($roomEvent, RoomEvent::TREMOR);
    }

    public function handleElectricArcEvents(array $rooms, \DateTime $date): void
    {
        /** @var Place $roomToElectrize */
        $roomToElectrize = $this->getRandomElementsFromArray->execute(
            elements: $rooms,
            number: 1
        )->first();
        if (!$roomToElectrize) {
            return;
        }

        $this->statusService->createStatusFromName(
            PlaceStatusEnum::SELECTED_FOR_ELECTROCUTION->toString(),
            $roomToElectrize,
            [EventEnum::NEW_CYCLE, RoomEvent::ELECTRIC_ARC],
            $date
        );

        $roomEvent = new RoomEvent(
            $roomToElectrize,
            [EventEnum::NEW_CYCLE, RoomEvent::ELECTRIC_ARC],
            $date
        );
        $this->eventService->callEvent($roomEvent, RoomEvent::ELECTRIC_ARC);
    }

    public function handleEquipmentBreak(ProbaCollection $equipments, Daedalus $daedalus, \DateTime $date): void
    {
        $equipmentToBreak = $this
            ->randomService
            ->getRandomDaedalusEquipmentFromProbaCollection(
                array: $equipments,
                number: 1,
                daedalus: $daedalus
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

    public function handleDoorBreak(array $doors, \DateTime $date): void
    {
        /** @var Door $doorToBreak */
        $doorToBreak = $this->getRandomElementsFromArray->execute(
            elements: $doors,
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

    public function handlePanicCrisis(array $players, \DateTime $date): void
    {
        /** @var Player $playerToPanic */
        $playerToPanic = $this->getRandomElementsFromArray->execute(
            elements: $players,
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

    public function handleMetalPlates(array $players, \DateTime $date): void
    {
        /** @var Player $playerToSteelPlate */
        $playerToSteelPlate = $this->getRandomElementsFromArray->execute(
            elements: $players,
            number: 1
        )->first();
        if (!$playerToSteelPlate) {
            return;
        }

        $tags = [EventEnum::NEW_CYCLE, PlayerEvent::METAL_PLATE];
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::SELECTED_FOR_STEEL_PLATE,
            $playerToSteelPlate,
            $tags,
            $date
        );

        $playerEvent = new PlayerEvent(
            $playerToSteelPlate,
            $tags,
            $date
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::METAL_PLATE);
    }

    public function handleCrewDisease(array $players, \DateTime $date): void
    {
        /** @var Player $playerToMakeSick */
        $playerToMakeSick = $this->getRandomElementsFromArray->execute(
            elements: $players,
            number: 1
        )->first();
        if (!$playerToMakeSick) {
            return;
        }

        $this->statusService->createStatusFromName(
            PlayerStatusEnum::SELECTED_FOR_BOARD_DISEASE,
            $playerToMakeSick,
            [EventEnum::NEW_CYCLE, PlayerEvent::CYCLE_DISEASE],
            $date
        );

        $playerEvent = new PlayerEvent(
            $playerToMakeSick,
            [EventEnum::NEW_CYCLE, PlayerEvent::CYCLE_DISEASE],
            $date
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::CYCLE_DISEASE);
    }

    public function handleOxygenTankBreak(array $tanks, \DateTime $date): void
    {
        /** @var GameEquipment $oxygenTankToBreak */
        $oxygenTankToBreak = $this->getRandomElementsFromArray->execute(
            elements: $tanks,
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

    public function handleFuelTankBreak(array $tanks, \DateTime $date): void
    {
        /** @var GameEquipment $fuelTankToBreak */
        $fuelTankToBreak = $this->getRandomElementsFromArray->execute(
            elements: $tanks,
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
}
