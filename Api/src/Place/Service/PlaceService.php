<?php

namespace Mush\Place\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Event\PlaceInitEvent;
use Mush\Place\Repository\PlaceRepository;

class PlaceService implements PlaceServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private PlaceRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PlaceRepository $repository,
    ) {
        $this->entityManager = $entityManager;
          $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
    }

    public function persist(Place $place): Place
    {
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        return $place;
    }

    public function findById(int $id): ?Place
    {
        $place = $this->repository->find($id);

        return $place instanceof Place ? $place : null;
    }

    public function createPlace(
        PlaceConfig $roomConfig,
        Daedalus $daedalus,
        string $reason,
        \DateTime $time
    ): Place {
        $room = new Place();
        $room->setName($roomConfig->getName());
        $room->setType($roomConfig->getType());

        $room->setDaedalus($daedalus);

        $this->persist($room);

        $placeEvent = new PlaceInitEvent($room, $roomConfig, $reason, $time);
        $this->eventDispatcher->dispatch($placeEvent, PlaceInitEvent::NEW_PLACE);

        return $room;
    }
}
