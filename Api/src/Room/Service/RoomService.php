<?php

namespace Mush\Room\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Enum\RoomEventEnum;
use Mush\Room\Repository\RoomRepository;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RoomService implements RoomServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RoomRepository $repository;
    private GameEquipmentServiceInterface $equipmentService;
    private StatusServiceInterface $statusService;
    private RandomServiceInterface $randomService;
    private GameConfig $gameConfig;

    /**
     * RoomService constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RoomRepository $repository,
        GameEquipmentServiceInterface $equipmentService,
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
        $this->statusService = $statusService;
        $this->randomService = $randomService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function persist(Room $room): Room
    {
        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return $room;
    }

    public function findById(int $id): ?Room
    {
        return $this->repository->find($id);
    }

    public function createRoom(RoomConfig $roomConfig, Daedalus $daedalus): Room
    {
        $room = new Room();
        $room->setName($roomConfig->getName());

        $this->persist($room);

        $doorConfig = $daedalus
            ->getGameConfig()
            ->getEquipmentsConfig()
            ->filter(fn (EquipmentConfig $item) => $item->getName() === EquipmentEnum::DOOR)->first()
        ;

        // @FIXME how to simplify that?
        foreach ($roomConfig->getDoors() as $doorName) {
            if (
            $roomDoor = $daedalus->getRooms()->filter( //If door already exist
                function (Room $room) use ($doorName) {
                    return $room->getDoors()->exists(function ($key, Door $door) use ($doorName) {
                        return $door->getName() === $doorName;
                    });
                }
            )->first()
            ) {
                $door = $roomDoor->getDoors()->filter(function (Door $door) use ($doorName) {
                    return $door->getName() === $doorName;
                })->first();
            } else { //else create new door
                $door = new Door();
                $door
                    ->setName($doorName)
                    ->setEquipment($doorConfig)
                ;
            }

            $room->addDoor($door);
        }

        foreach ($roomConfig->getItems() as $itemName) {
            $item = $daedalus
                ->getGameConfig()
                ->getEquipmentsConfig()
                ->filter(fn (EquipmentConfig $item) => $item->getName() === $itemName)->first()
            ;
            $gameItem = $this->equipmentService->createGameEquipment($item, $daedalus);

            //@TODO better handle this
            if ($item->getMechanicByName(EquipmentMechanicEnum::PLANT) &&
                $youngStatus = $gameItem->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG)) {
                $youngStatus->setGameEquipment(null);
                $this->statusService->delete($youngStatus);
            }

            $room->addEquipment($gameItem);
        }

        foreach ($roomConfig->getEquipments() as $equipmentName) {
            $equipment = $daedalus
                ->getGameConfig()
                ->getEquipmentsConfig()
                ->filter(fn (EquipmentConfig $equipment) => $equipment->getName() === $equipmentName)->first()
            ;
            $gameEquipment = $this->equipmentService->createGameEquipment($equipment, $daedalus);
            $room->addEquipment($gameEquipment);
        }

        return $this->persist($room);
    }




    public function handleIncident(Room $room,  \DateTime $date): Room
    {
        //Tremors
        if ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getTremorRate())){
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }

        //Electric Arcs
        if ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getElectricArcRate())){
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::TREMOR);
        }
        
        //Fire
        $this->handleFire($room, $date);

        return $room;
    }



    public function handleFire(Room $room, \DateTime $date): Room
    {
        $fireStatus = $room->getStatusByName(StatusEnum::FIRE);
        if ($fireStatus && !$fireStatus instanceof ChargeStatus) {
            throw new \LogicException('Fire is not a ChargedStatus');
        }

        if ($fireStatus && $fireStatus->getCharge() === 0) {
            //there is already a fire in the room
            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::FIRE);

        //a secondary fire already started in this room this cycle OR no fire
        } elseif ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getStartingFireRate())) {
            $roomEvent = new RoomEvent($room, $date);
            $roomEvent->setReason(RoomEventEnum::CYCLE_FIRE);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);

            $roomEvent = new RoomEvent($room, $date);
            $this->eventDispatcher->dispatch($roomEvent, RoomEvent::FIRE);
        }

        return $room;
    }

    public function propagateFire(Room $room, \DateTime $date): Room
    {
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getPropagatingFireRate())) {
                $roomEvent = new RoomEvent($adjacentRoom, $date);
                $roomEvent->setReason(RoomEventEnum::PROPAGATING_FIRE);
                $this->eventDispatcher->dispatch($roomEvent, RoomEvent::STARTING_FIRE);
            }
        }

        return $room;
    }
}
