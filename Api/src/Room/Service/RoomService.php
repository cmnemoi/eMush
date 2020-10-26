<?php

namespace Mush\Room\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Item;
use Mush\Item\Service\PlantServiceInterface;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Room\Entity\Door;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Repository\RoomRepository;

class RoomService implements RoomServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RoomRepository $repository;
    private GameItemServiceInterface $itemService;
    private PlantServiceInterface $fruitService;

    /**
     * RoomService constructor.
     * @param EntityManagerInterface $entityManager
     * @param RoomRepository $repository
     * @param GameItemServiceInterface $itemService
     * @param PlantServiceInterface $fruitService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RoomRepository $repository,
        GameItemServiceInterface $itemService,
        PlantServiceInterface $fruitService
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

        // @FIXME how to simplify that?
        foreach ($roomConfig->getDoors() as $doorName) {
            if ($roomDoor = $daedalus->getRooms()->filter( //If door already exist
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
            } else { //else create new door
                $door = new Door();
                $door->setName($doorName);
            }

            $room->addDoor($door);
        }

        foreach ($roomConfig->getItems() as $itemName) {
            $item = $daedalus
                ->getGameConfig()
                ->getItemsConfig()
                ->filter(fn(Item $item) => $item->getName() === $itemName)->first()
            ;
            $gameItem = $this->itemService->createGameItem($item);
            $room->addItem($gameItem);
        }

        return $this->persist($room);
    }
}
