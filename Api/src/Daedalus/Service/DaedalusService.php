<?php

namespace Mush\Daedalus\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\DAaedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Game\Service\CycleServiceInterface;
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

    private DaedalusConfigServiceInterface $daedalusConfigService;

    /**
     * DaedalusService constructor.
     * @param EntityManagerInterface $entityManager
     * @param DaedalusRepository $repository
     * @param RoomServiceInterface $roomService
     * @param CycleServiceInterface $cycleService
     * @param DaedalusConfigServiceInterface $daedalusConfigService
     */
    public function __construct(EntityManagerInterface $entityManager, DaedalusRepository $repository, RoomServiceInterface $roomService, CycleServiceInterface $cycleService, DaedalusConfigServiceInterface $daedalusConfigService)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->roomService = $roomService;
        $this->cycleService = $cycleService;
        $this->daedalusConfigService = $daedalusConfigService;
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

        $daedalusConfig = $this->daedalusConfigService->getConfig();

        $daedalus
            ->setCycle($this->cycleService->getCycleFromDate(new \DateTime()))
            ->setOxygen($daedalusConfig->getInitOxygen())
            ->setFuel($daedalusConfig->getInitFuel())
            ->setHull($daedalusConfig->getInitHull())
            ->setShield($daedalusConfig->getInitShield())
        ;

        $this->persist($daedalus);

        /** @var RoomConfig $roomconfig */
        foreach ($daedalusConfig->getRooms() as $roomconfig) {
            $room = $this->roomService->createRoom($roomconfig, $daedalus);
            $daedalus->addRoom($room);
        }

        $daedalusEvent = new DaedalusEvent($daedalus);
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::NEW_DAEDALUS);

        return $this->persist($daedalus);
    }
}