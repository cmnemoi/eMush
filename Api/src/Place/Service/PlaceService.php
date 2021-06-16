<?php

namespace Mush\Place\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Repository\PlaceRepository;
use Mush\Status\Enum\EquipmentStatusEnum;

class PlaceService implements PlaceServiceInterface
{
    private EntityManagerInterface $entityManager;
    private PlaceRepository $repository;
    private GameEquipmentServiceInterface $equipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PlaceRepository $repository,
        GameEquipmentServiceInterface $equipmentService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
    }

    public function persist(Place $place): Place
    {
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        return $place;
    }

    public function findById(int $id): ?Place
    {
        return $this->repository->find($id);
    }

    public function createPlace(PlaceConfig $roomConfig, Daedalus $daedalus): Place
    {
        $room = new Place();
        $room->setName($roomConfig->getName());
        $room->setType($roomConfig->getType());

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
                function (Place $room) use ($doorName) {
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
                    ->setPlace($room)
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
                $gameItem->removeStatus($youngStatus);
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
}
