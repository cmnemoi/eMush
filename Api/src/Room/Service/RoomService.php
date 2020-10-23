<?php

namespace Mush\Room\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Item\Service\FruitServiceInterface;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Room\Entity\Door;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Repository\RoomRepository;

class RoomService implements RoomServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RoomRepository $repository;
    private ItemServiceInterface $itemService;
    private FruitServiceInterface $fruitService;

    /**
     * RoomService constructor.
     * @param EntityManagerInterface $entityManager
     * @param RoomRepository $repository
     * @param ItemServiceInterface $itemService
     * @param FruitServiceInterface $fruitService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RoomRepository $repository,
        ItemServiceInterface $itemService,
        FruitServiceInterface $fruitService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->itemService = $itemService;
        $this->fruitService = $fruitService;
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

        foreach ($roomConfig->getDoors() as $doorName) {
            if ($roomDoor = $daedalus->getRooms()->filter(
                function (Room $room) use ($doorName) {
                    return $room->getDoors()->exists(function ($key, Door $door) use ($doorName) {
                        return ($door->getName() === $doorName);
                    });
                }
            )->first()
            ) {
                $door = $roomDoor->getDoors()->filter(function (Door $door) use ($doorName) {
                    return ($door->getName() === $doorName);
                })->first();
            } else {
                $door = new Door();
                $door->setName($doorName);
            }

            $room->addDoor($door);
        }

        foreach ($roomConfig->getItems() as $itemName) {
            if (in_array($itemName, GamePlantEnum::getAll())) {
                $item = $this->fruitService->createPlantFromName($itemName, $daedalus);
                $item->setStatuses([]);
            } else {
                $item = $this->itemService->createItem($itemName);
            }
            $room->addItem($item);
        }

        return $this->persist($room);
    }
}
