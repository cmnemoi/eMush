<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Game\Service\EventServiceInterface;

class DaedalusService implements DaedalusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private DaedalusRepository $repository;
    private CycleServiceInterface $cycleService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        DaedalusRepository $repository,
        CycleServiceInterface $cycleService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->repository = $repository;
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
        $daedalus = $this->repository->find($id);

        return $daedalus instanceof Daedalus ? $daedalus : null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function findByCriteria(DaedalusCriteria $criteria): DaedalusCollection
    {
        return new DaedalusCollection();
    }

    public function findAvailableDaedalus(string $name): ?Daedalus
    {
        return $this->repository->findAvailableDaedalus($name);
    }

    public function existAvailableDaedalus(): bool
    {
        return $this->repository->existAvailableDaedalus();
    }

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection
    {
        return $daedalus->getGameConfig()->getCharactersConfig()->filter(
            fn (CharacterConfig $characterConfig) => !$daedalus->getPlayers()->exists(
                fn (int $key, Player $player) => ($player->getCharacterConfig()->getName() === $characterConfig->getName())
            )
        );
    }

    public function createDaedalus(GameConfig $gameConfig, string $name): Daedalus
    {
        $daedalus = new Daedalus();

        $daedalusConfig = $gameConfig->getDaedalusConfig();

        $daedalus
            ->setName($name)
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

        $daedalusEvent = new DaedalusInitEvent(
            $daedalus,
            $daedalusConfig,
            EventEnum::CREATE_DAEDALUS,
            new \DateTime()
        );

        $this->eventService->callEvent($daedalusEvent, DaedalusInitEvent::NEW_DAEDALUS);

        return $daedalus;
    }

    public function startDaedalus(Daedalus $daedalus): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        $time = new \DateTime();
        $daedalus->setCreatedAt($time);
        $daedalus->setCycle($this->cycleService->getInDayCycleFromDate($time, $gameConfig));
        $daedalus->setCycleStartedAt($this->cycleService->getDaedalusStartingCycleDate($daedalus));

        $daedalus->setGameStatus(GameStatusEnum::STARTING);

        $this->persist($daedalus);

        return $daedalus;
    }

    public function selectAlphaMush(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        // Chose alpha Mushs
        $chancesArray = [];

        foreach ($gameConfig->getCharactersConfig() as $characterConfig) {
            // @TODO lower $mushChance if user is a beginner
            // @TODO (maybe add a "I want to be mush" setting to increase this proba)

            $mushChance = 1;
            if (!$characterConfig->getInitStatuses()
                ->filter(fn (StatusConfig $statusConfig) => $statusConfig->getName() === PlayerStatusEnum::IMMUNIZED)->isEmpty()
            ) {
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
                $playerEvent = new PlayerEvent(
                    $mushPlayers->first(),
                    DaedalusEvent::FULL_DAEDALUS,
                    $date
                );
                $this->eventService->callEvent($playerEvent, PlayerEvent::CONVERSION_PLAYER);
            }
        }

        return $daedalus;
    }

    public function getRandomAsphyxia(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        $player = $this->getRandomPlayersWithLessOxygen($daedalus);

        if ($this->getOxygenCapsuleCount($player) === 0) {
            $playerEvent = new PlayerEvent(
                $player,
                EndCauseEnum::ASPHYXIA,
                $date
            );

            $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
        } else {
            $capsule = $player->getEquipments()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->first();

            $this->gameEquipmentService->delete($capsule);

            $this->roomLogService->createLog(
                LogEnum::OXY_LOW_USE_CAPSULE,
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                'event_log',
                $player,
                [],
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
        return $player->getEquipments()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->count();
    }

    public function killRemainingPlayers(Daedalus $daedalus, string $cause, \DateTime $date): Daedalus
    {
        $playerAliveNb = $daedalus->getPlayers()->getPlayerAlive()->count();
        for ($i = 0; $i < $playerAliveNb; ++$i) {
            $player = $this->randomService->getAlivePlayerInDaedalus($daedalus);

            $playerEvent = new PlayerEvent(
                $player,
                $cause,
                $date
            );
            $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
        }

        return $daedalus;
    }

    public function changeOxygenLevel(Daedalus $daedalus, int $change): Daedalus
    {
        $maxOxygen = $daedalus->getGameConfig()->getDaedalusConfig()->getMaxOxygen();
        $newOxygenLevel = $daedalus->getOxygen() + $change;

        if ($newOxygenLevel > $maxOxygen) {
            $daedalus->setOxygen($maxOxygen);
        } elseif ($newOxygenLevel < 0) {
            $daedalus->setOxygen(0);
        } else {
            $daedalus->setOxygen($newOxygenLevel);
        }

        return $daedalus;
    }

    public function changeFuelLevel(Daedalus $daedalus, int $change): Daedalus
    {
        $maxFuel = $daedalus->getGameConfig()->getDaedalusConfig()->getMaxFuel();
        $newFuelLevel = $daedalus->getFuel() + $change;

        if ($newFuelLevel > $maxFuel) {
            $daedalus->setFuel($maxFuel);
        } elseif ($newFuelLevel < 0) {
            $daedalus->setFuel(0);
        } else {
            $daedalus->setFuel($newFuelLevel);
        }

        return $daedalus;
    }

    public function changeHull(Daedalus $daedalus, int $change, \DateTime $date): Daedalus
    {
        $maxHull = $daedalus->getGameConfig()->getDaedalusConfig()->getMaxHull();
        if (($newHull = $daedalus->getHull() + $change) < 0) {
            $daedalus->setHull(0);

            $daedalusEvent = new DaedalusEvent(
                $daedalus,
                EndCauseEnum::DAEDALUS_DESTROYED,
                $date
            );

            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::END_DAEDALUS);
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
