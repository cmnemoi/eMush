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

    public function handleFire(Room $room): Room
    {
        $fireStatus = $room->getStatusByName(StatusEnum::FIRE);

        if ($fireStatus && !$fireStatus instanceof ChargeStatus) {
            throw new \LogicException('Fire is not a ChargedStatus');
        }

        if ($fireStatus && $fireStatus->getCharge() === 0) {
            $this->propagateFire($room);
        } elseif ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getStartingFireRate())) {
            $fireStatus = $this->startFire($room);

            //primary fire deal damage on the first cycle
            $fireStatus->setCharge(0);
            $this->propagateFire($room);
        }

        return $room;
    }

    public function startFire(Room $room): ChargeStatus
    {
        if (!$room->hasStatus(StatusEnum::FIRE)) {
            $fireStatus = $this->statusService->createChargeRoomStatus(StatusEnum::FIRE,
                    $room,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    VisibilityEnum::PUBLIC,
                    1);
        } else {
            $fireStatus = $room->getStatusByName(StatusEnum::FIRE);

            if (!$fireStatus instanceof ChargeStatus) {
                throw new \LogicException('Fire is not a charged Status');
            }

            $fireStatus->setCharge(0);
        }

        $this->persist($room);

        return $fireStatus;
    }

    public function propagateFire(Room $room): Room
    {
        foreach ($room->getDoors() as $door) {
            $adjacentRoom = $door->getOtherRoom($room);

            if ($this->randomService->isSuccessfull($this->gameConfig->getDifficultyConfig()->getPropagatingFireRate())) {
                $this->startFire($adjacentRoom);
            }
        }
        $this->persist($room);

        return $room;
    }
}
