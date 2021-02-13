<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Criteria\GameEquipmentCriteria;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Place\Event\RoomEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DaedalusIncidentService implements DaedalusIncidentServiceInterface
{
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;
    private GameEquipmentRepository $gameEquipmentRepository;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentRepository $gameEquipmentRepository
    ) {
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
        $this->gameEquipmentRepository = $gameEquipmentRepository;
    }

    public function handleFireEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewFire = $this->getNumberOfIncident($daedalus);

        $newFireRooms = $this->randomService->getRandomElements($daedalus->getRooms()->toArray(), $numberOfNewFire);

        /** @var Place $room */
        foreach ($newFireRooms as $room) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::CYCLE_FIRE);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
        }

        return $numberOfNewFire;
    }

    public function handleTremorEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewTremor = $this->getNumberOfIncident($daedalus);

        $newTremorRooms = $this->randomService->getRandomElements($daedalus->getRooms()->toArray(), $numberOfNewTremor);

        /** @var Place $room */
        foreach ($newTremorRooms as $room) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::TREMOR);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        return $numberOfNewTremor;
    }

    public function handleElectricArcEvents(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfNewElectricArcs = $this->getNumberOfIncident($daedalus);

        $newElectricArcs = $this->randomService->getRandomElements($daedalus->getRooms()->toArray(), $numberOfNewElectricArcs);

        /** @var Place $room */
        foreach ($newElectricArcs as $room) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::ELECTRIC_ARC);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::ELECTRIC_ARC);
        }

        return $numberOfNewElectricArcs;
    }

    public function handleEquipmentBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfEquipmentBroken = $this->getNumberOfIncident($daedalus);

        if ($numberOfEquipmentBroken > 0) {
            $criteria = new GameEquipmentCriteria($daedalus);
            $criteria->setNotInstanceOf([Door::class]);
            $criteria->setBreakable(true);

            $daedalusEquipments = $this->gameEquipmentRepository->findByCriteria($criteria);

            $brokenEquipments = $this->randomService->getRandomElements($daedalusEquipments, $numberOfEquipmentBroken);

            foreach ($brokenEquipments as $gameEquipment) {
                $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::HIDDEN, $date);
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }

        return $numberOfEquipmentBroken;
    }

    public function handleDoorBreak(Daedalus $daedalus, \DateTime $date): int
    {
        $numberOfDoorBroken = $this->getNumberOfIncident($daedalus);

        if ($numberOfDoorBroken > 0) {
            $criteria = new GameEquipmentCriteria($daedalus);
            $criteria->setInstanceOf([Door::class]);
            $criteria->setBreakable(true);

            $daedalusDoor = $this->gameEquipmentRepository->findByCriteria($criteria);

            $brokenDoors = $this->randomService->getRandomElements($daedalusDoor, $numberOfDoorBroken);

            foreach ($brokenDoors as $door) {
                $equipmentEvent = new EquipmentEvent($door, VisibilityEnum::HIDDEN, $date);
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            }
        }

        return $numberOfDoorBroken;
    }

    //Each cycle get 0 to day event
    //@TODO: to be improved
    private function getNumberOfIncident(Daedalus $daedalus): int
    {
        return $this->randomService->random(0, $daedalus->getDay());
    }
}
