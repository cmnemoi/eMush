<?php

namespace Mush\Daedalus\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\DAaedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Item\Service\GameFruitServiceInterface;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Service\RoomServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusService implements DaedalusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private DaedalusRepository $repository;
    private RoomServiceInterface $roomService;
    private CycleServiceInterface $cycleService;
    private ItemServiceInterface $itemService;
    private GameFruitServiceInterface $gameFruitService;
    private DaedalusConfig $daedalusConfig;

    /**
     * DaedalusService constructor.
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param DaedalusRepository $repository
     * @param RoomServiceInterface $roomService
     * @param CycleServiceInterface $cycleService
     * @param ItemServiceInterface $itemService
     * @param GameFruitServiceInterface $gameFruitService
     * @param DaedalusConfigServiceInterface $daedalusConfigService
     */
    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, DaedalusRepository $repository, RoomServiceInterface $roomService, CycleServiceInterface $cycleService, ItemServiceInterface $itemService, GameFruitServiceInterface $gameFruitService, DaedalusConfigServiceInterface $daedalusConfigService)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->roomService = $roomService;
        $this->cycleService = $cycleService;
        $this->itemService = $itemService;
        $this->gameFruitService = $gameFruitService;
        $this->daedalusConfig = $daedalusConfigService->getConfig();
    }

    public function persist(Daedalus $daedalus): Daedalus
    {
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();

        return $daedalus;
    }

    public function findById(int $id): ?Daedalus
    {
        return $this->repository->find($id);
    }

    // @TODO
    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection
    {
        return new DaedalusCollection();
    }

    public function createDaedalus(): Daedalus
    {
        $daedalus = new Daedalus();

        $daedalus
            ->setCycle($this->cycleService->getCycleFromDate(new \DateTime()))
            ->setOxygen($this->daedalusConfig->getInitOxygen())
            ->setFuel($this->daedalusConfig->getInitFuel())
            ->setHull($this->daedalusConfig->getInitHull())
            ->setShield($this->daedalusConfig->getInitShield())
        ;

        $this->persist($daedalus);

        $this->gameFruitService->initGameFruits($daedalus);

        /** @var RoomConfig $roomconfig */
        foreach ($this->daedalusConfig->getRooms() as $roomconfig) {
            $room = $this->roomService->createRoom($roomconfig, $daedalus);
            $daedalus->addRoom($room);
        }

        $daedalusEvent = new DaedalusEvent($daedalus);
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::NEW_DAEDALUS);

        return $this->persist($daedalus);
    }
}