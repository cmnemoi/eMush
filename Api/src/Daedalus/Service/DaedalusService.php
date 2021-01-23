<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Service\RoomServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusService implements DaedalusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private DaedalusRepository $repository;
    private RoomServiceInterface $roomService;
    private CycleServiceInterface $cycleService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        DaedalusRepository $repository,
        RoomServiceInterface $roomService,
        CycleServiceInterface $cycleService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->roomService = $roomService;
        $this->cycleService = $cycleService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
    }

    /**
     * @codeCoverageIgnore
     */
    public function persist(Daedalus $daedalus): Daedalus
    {
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();

        return $daedalus;
    }

    /**
     * @codeCoverageIgnore
     */
    public function findById(int $id): ?Daedalus
    {
        return $this->repository->find($id);
    }

    /**
     * @codeCoverageIgnore
     */
    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection
    {
        return new DaedalusCollection();
    }

    public function findAvailableDaedalus(): ?Daedalus
    {
        return $this->repository->findAvailableDaedalus();
    }

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection
    {
        return $daedalus->getGameConfig()->getCharactersConfig()->filter(
            fn (CharacterConfig $characterConfig) => !$daedalus->getPlayers()->exists(
                fn (int $key, Player $player) => ($player->getCharacterConfig()->getName() === $characterConfig->getName())
            )
        );
    }

    public function createDaedalus(GameConfig $gameConfig): Daedalus
    {
        $daedalus = new Daedalus();

        $daedalusConfig = $gameConfig->getDaedalusConfig();

        $daedalus
            ->setGameConfig($gameConfig)
            ->setCycle($this->cycleService->getCycleFromDate(new \DateTime(), $gameConfig))
            ->setOxygen($daedalusConfig->getInitOxygen())
            ->setFuel($daedalusConfig->getInitFuel())
            ->setHull($daedalusConfig->getInitHull())
            ->setShield($daedalusConfig->getInitShield())
            ->setSpores($daedalusConfig->getDailySporeNb())
            ->setDailySpores($daedalusConfig->getDailySporeNb())
        ;

        $this->persist($daedalus);

        /** @var RoomConfig $roomconfig */
        foreach ($daedalusConfig->getRoomConfigs() as $roomconfig) {
            $room = $this->roomService->createRoom($roomconfig, $daedalus);
            $daedalus->addRoom($room);
        }

        $randomItemPlaces = $daedalusConfig->getRandomItemPlace();
        if (null !== $randomItemPlaces) {
            foreach ($randomItemPlaces->getItems() as $itemName) {
                $item = $daedalus
                    ->getGameConfig()
                    ->getEquipmentsConfig()
                    ->filter(fn (EquipmentConfig $item) => $item->getName() === $itemName)
                    ->first()
                ;
                $item = $this->gameEquipmentService->createGameEquipment($item, $daedalus);
                $roomName = $randomItemPlaces
                    ->getPlaces()[$this->randomService->random(0, count($randomItemPlaces->getPlaces()) - 1)]
                ;
                $room = $daedalus->getRooms()->filter(fn (Room $room) => $roomName === $room->getName())->first();
                $item->setRoom($room);
                $this->gameEquipmentService->persist($item);
            }
        }

        $daedalusEvent = new DaedalusEvent($daedalus);
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::NEW_DAEDALUS);

        return $this->persist($daedalus);
    }

    public function selectAlphaMush(Daedalus $daedalus): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        //Chose alpha Mushs
        $chancesArray = [];

        foreach ($gameConfig->getCharactersConfig() as $characterConfig) {
            //@TODO lower $mushChance if user is a beginner
            //@TODO (maybe add a "I want to be mush" setting to increase this proba)

            $mushChance = 1;
            if (in_array(PlayerStatusEnum::IMMUNIZED, $characterConfig->getStatuses())) {
                $mushChance = 0;
            }
            $chancesArray[$characterConfig->getName()] = $mushChance;
        }

        $mushNumber = $gameConfig->getNbMush();

        $mushPlayerName = $this->randomService->getRandomElementsFromProbaArray($chancesArray, $mushNumber);
        foreach ($mushPlayerName as $playerName) {
            $mushPlayers = $daedalus
                ->getPlayers()
                ->filter(fn (Player $player) => $player->getCharacterConfig()->getName() === $playerName)
            ;

            if (!$mushPlayers->isEmpty()) {
                $playerEvent = new PlayerEvent($mushPlayers->first());
                $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);
            }
        }

        return $daedalus;
    }

    public function getRandomAsphyxia(Daedalus $daedalus, \DateTime $date = null): Daedalus
    {
        $date = $date ?? new \DateTime('now');
        $chancesArray = [];
        /** @var Player $player */
        foreach ($daedalus->getPlayers()->getPlayerAlive() as $player) {
            if (!$player->getItems()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->isEmpty()) {
                $capsule = $player->getItems()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->first();
                $capsule->removeLocation();
                $this->gameEquipmentService->delete($capsule);

                $this->roomLogService->createPlayerLog(
                    LogEnum::OXY_LOW_USE_CAPSULE,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    $date
                );
            } else {
                $chancesArray[$player->getCharacterConfig()->getName()] = 1;
            }
        }

        $playerName = $this->randomService->getSingleRandomElementFromProbaArray($chancesArray);

        $player = $daedalus
            ->getPlayers()
            ->filter(fn (Player $player) => $player->getCharacterConfig()->getName() === $playerName)->first()
        ;

        $playerEvent = new PlayerEvent($player);
        $playerEvent->setReason(EndCauseEnum::ASPHYXIA);

        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

        return $daedalus;
    }

    public function killRemainingPlayers(Daedalus $daedalus, string $cause): Daedalus
    {
        $playerAliveNb = $daedalus->getPlayers()->getPlayerAlive()->count();
        for ($i = 0; $i < $playerAliveNb; ++$i) {
            $player = $this->randomService->getAlivePlayerInDaedalus($daedalus);

            $playerEvent = new PlayerEvent($player);
            $playerEvent->setReason($cause);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }

        return $daedalus;
    }

    public function changeOxygenLevel(Daedalus $daedalus, int $change): Daedalus
    {
        $maxOxygen = $daedalus->getGameConfig()->getMaxOxygen();
        $newOxygenLevel = $daedalus->getOxygen() + $change;
        if ($newOxygenLevel <= $maxOxygen && $newOxygenLevel >= 0) {
            $daedalus->setOxygen($newOxygenLevel);
        }

        return $daedalus;
    }

    public function changeFuelLevel(Daedalus $daedalus, int $change): Daedalus
    {
        $maxFuel = $daedalus->getGameConfig()->getMaxFuel();
        if (!($newFuelLevel = $daedalus->getFuel() + $change > $maxFuel) && !($newFuelLevel < 0)) {
            $daedalus->addFuel($change);
        }

        return $daedalus;
    }
}
