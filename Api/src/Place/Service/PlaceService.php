<?php

namespace Mush\Place\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Event\PlaceInitEvent;
use Mush\Place\Repository\PlaceRepository;

class PlaceService implements PlaceServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private PlaceRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        PlaceRepository $repository,
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->repository = $repository;
    }

    public function persist(Place $place): Place
    {
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        return $place;
    }

    public function delete(Place $place): bool
    {
        $daedalus = $place->getDaedalus();
        $daedalus->removePlace($place);
        $this->entityManager->persist($daedalus);

        $this->entityManager->remove($place);
        $this->entityManager->flush();

        return true;
    }

    public function findById(int $id): ?Place
    {
        $place = $this->repository->find($id);

        return $place instanceof Place ? $place : null;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ?Place
    {
        $place = $this->repository->findByNameAndDaedalus($name, $daedalus);

        return $place instanceof Place ? $place : null;
    }

    public function createPlace(
        PlaceConfig $roomConfig,
        Daedalus $daedalus,
        array $reasons,
        \DateTime $time
    ): Place {
        $room = new Place();
        $room->setName($roomConfig->getPlaceName());
        $room->setType($roomConfig->getType());

        $room->setDaedalus($daedalus);

        $this->persist($room);

        $placeEvent = new PlaceInitEvent($room, $roomConfig, $reasons, $time);
        $this->eventService->callEvent($placeEvent, PlaceInitEvent::NEW_PLACE);

        return $room;
    }
}
