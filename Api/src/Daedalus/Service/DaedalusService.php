<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
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
    private PlaceServiceInterface $placesService;
    private CycleServiceInterface $cycleService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        DaedalusRepository $repository,
        PlaceServiceInterface $placesService,
        CycleServiceInterface $cycleService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->placesService = $placesService;
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
            ->setCycle(0)
            ->setOxygen($daedalusConfig->getInitOxygen())
            ->setFuel($daedalusConfig->getInitFuel())
            ->setHull($daedalusConfig->getInitHull())
            ->setShield($daedalusConfig->getInitShield())
            ->setSpores($daedalusConfig->getDailySporeNb())
            ->setDailySpores($daedalusConfig->getDailySporeNb())
        ;

        $this->createNeron($daedalus);

        $this->persist($daedalus);

        /** @var PlaceConfig $placeConfig */
        foreach ($daedalusConfig->getPlaceConfigs() as $placeConfig) {
            $place = $this->placesService->createPlace($placeConfig, $daedalus);
            $daedalus->addPlace($place);
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
                $room = $daedalus->getRooms()->filter(fn (Place $room) => $roomName === $room->getName())->first();
                $item->setPlace($room);
                $this->gameEquipmentService->persist($item);
            }
        }

        $daedalusEvent = new DaedalusEvent($daedalus, new \DateTime());
        $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::NEW_DAEDALUS);

        return $this->persist($daedalus);
    }

    public function startDaedalus(Daedalus $daedalus): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        $daedalus
            ->setCycle($this->cycleService->getInDayCycleFromDate(new \DateTime(), $gameConfig))
            ->setCycleStartedAt($this->cycleService->getDaedalusStartingCycleDate($daedalus))
        ;

        $daedalus->setGameStatus(GameStatusEnum::STARTING);

        return $daedalus;
    }

    public function selectAlphaMush(Daedalus $daedalus, \DateTime $date): Daedalus
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
                $playerEvent = new PlayerEvent($mushPlayers->first(), $date);
                $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);
            }
        }

        return $daedalus;
    }

    public function getRandomAsphyxia(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        $player = $this->getRandomPlayersWithLessOxygen($daedalus);

        if ($this->getOxygenCapsuleCount($player) === 0) {
            $playerEvent = new PlayerEvent($player, $date);
            $playerEvent->setReason(EndCauseEnum::ASPHYXIA);

            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        } else {
            $capsule = $player->getItems()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->first();
            $capsule->removeLocation();
            $this->gameEquipmentService->delete($capsule);

            $this->roomLogService->createLog(
                LogEnum::OXY_LOW_USE_CAPSULE,
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'event_log',
                $player,
                null,
                null,
                $date
            );
        }

        return $daedalus;
    }

    private function getRandomPlayersWithLessOxygen(Daedalus $daedalus): Player
    {
        $playersAlive = $daedalus->getPlayers()->getPlayerAlive();
        for ($i = 0; $i <= $daedalus->getGameConfig()->getMaxItemInInventory(); ++$i) {
            $players = $playersAlive->filter(fn (Player $player) => $this->getOxygenCapsuleCount($player) === $i);
            if ($players && !$players->isEmpty()) {
                return $this->randomService->getRandomPlayer($players);
            }
        }

        throw new \LogicException('this Daedalus is already empty');
    }

    private function getOxygenCapsuleCount(Player $player): int
    {
        return $player->getItems()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->count();
    }

    public function killRemainingPlayers(Daedalus $daedalus, string $cause, \DateTime $date): Daedalus
    {
        $playerAliveNb = $daedalus->getPlayers()->getPlayerAlive()->count();
        for ($i = 0; $i < $playerAliveNb; ++$i) {
            $player = $this->randomService->getAlivePlayerInDaedalus($daedalus);

            $playerEvent = new PlayerEvent($player, $date);
            $playerEvent->setReason($cause);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }

        return $daedalus;
    }

    public function changeOxygenLevel(Daedalus $daedalus, int $change): Daedalus
    {
        $maxOxygen = $daedalus->getGameConfig()->getDaedalusConfig()->getMaxOxygen();
        $newOxygenLevel = $daedalus->getOxygen() + $change;
        if ($newOxygenLevel <= $maxOxygen && $newOxygenLevel >= 0) {
            $daedalus->setOxygen($newOxygenLevel);
        }

        return $daedalus;
    }

    public function changeFuelLevel(Daedalus $daedalus, int $change): Daedalus
    {
        $maxFuel = $daedalus->getGameConfig()->getDaedalusConfig()->getMaxFuel();
        if (!($newFuelLevel = $daedalus->getFuel() + $change > $maxFuel) && $newFuelLevel >= 0) {
            $daedalus->addFuel($change);
        }

        return $daedalus;
    }

    public function changeHull(Daedalus $daedalus, int $change, \DateTime $date): Daedalus
    {
        $maxHull = $daedalus->getGameConfig()->getDaedalusConfig()->getMaxHull();
        if (($newHull = $daedalus->getHull() + $change) < 0) {
            $daedalus->setHull(0);

            $daedalusEvent = new DaedalusEvent($daedalus, $date);
            $daedalusEvent->setReason(EndCauseEnum::DAEDALUS_DESTROYED);

            $this->eventDispatcher->dispatch($daedalusEvent, DaedalusEvent::END_DAEDALUS);
        } else {
            $daedalus->setHull(min($newHull, $maxHull));
        }

        $this->persist($daedalus);

        return $daedalus;
    }

    private function createNeron(Daedalus $daedalus): void
    {
        $neron = new Neron();
        $neron->setDaedalus($daedalus);
        $daedalus->setNeron($neron);

        $this->entityManager->persist($neron);
    }
}
