<?php

namespace Mush\Daedalus\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Collection\DaedalusCollection;
use Mush\Daedalus\Entity\Criteria\DaedalusCriteria;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Entity\TitlePriority;
use Mush\Daedalus\Enum\CharacterSetEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusInitEvent;
use Mush\Daedalus\Repository\DaedalusInfoRepository;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Repository\TitlePriorityRepositoryInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Repository\LocalizationConfigRepository;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class DaedalusService implements DaedalusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private DaedalusRepository $repository;
    private CycleServiceInterface $cycleService;
    private RandomServiceInterface $randomService;
    private LocalizationConfigRepository $localizationConfigRepository;
    private DaedalusInfoRepository $daedalusInfoRepository;
    private DaedalusRepository $daedalusRepository;
    private TitlePriorityRepositoryInterface $titlePriorityRepository;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        DaedalusRepository $repository,
        CycleServiceInterface $cycleService,
        RandomServiceInterface $randomService,
        LocalizationConfigRepository $localizationConfigRepository,
        DaedalusInfoRepository $daedalusInfoRepository,
        DaedalusRepository $daedalusRepository,
        TitlePriorityRepositoryInterface $titlePriorityRepository,
        PlayerServiceInterface $playerService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->repository = $repository;
        $this->cycleService = $cycleService;
        $this->randomService = $randomService;
        $this->localizationConfigRepository = $localizationConfigRepository;
        $this->daedalusInfoRepository = $daedalusInfoRepository;
        $this->daedalusRepository = $daedalusRepository;
        $this->titlePriorityRepository = $titlePriorityRepository;
        $this->playerService = $playerService;
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

    public function persistDaedalusInfo(DaedalusInfo $daedalusInfo): DaedalusInfo
    {
        $this->entityManager->persist($daedalusInfo);
        $this->entityManager->flush();

        return $daedalusInfo;
    }

    public function delete(Daedalus $daedalus): Daedalus
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusInfo->deleteDaedalus();

        $this->persistDaedalusInfo($daedalusInfo);

        $this->entityManager->remove($daedalus);
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
        return $this->daedalusInfoRepository->findAvailableDaedalus($name)?->getDaedalus();
    }

    public function findAvailableDaedalusInLanguage(string $language): ?Daedalus
    {
        $daedalusInfo = $this->daedalusInfoRepository->findAvailableDaedalusInLanguage($language);

        return $daedalusInfo?->getDaedalus();
    }

    public function findAvailableDaedalusInLanguageForUser(string $language, User $user): ?Daedalus
    {
        $daedalusInfo = $this->daedalusInfoRepository->findAvailableDaedalusInLanguageForUser($language, $user);

        if ($daedalusInfo === null) {
            return null;
        }

        return $daedalusInfo->getDaedalus();
    }

    public function existAvailableDaedalus(): bool
    {
        return $this->daedalusInfoRepository->existAvailableDaedalus();
    }

    public function existAvailableDaedalusInLanguage(string $language): bool
    {
        return $this->daedalusInfoRepository->existAvailableDaedalusInLanguage($language);
    }

    public function existAvailableDaedalusWithName(string $name): bool
    {
        return $this->daedalusInfoRepository->existAvailableDaedalusWithName($name);
    }

    public function findAvailableCharacterForDaedalus(Daedalus $daedalus): Collection
    {
        return $daedalus->getAvailableCharacters()->filter(
            static fn (CharacterConfig $characterConfig) => !$daedalus->getPlayers()->exists(
                static fn (int $key, Player $player) => ($player->getName() === $characterConfig->getCharacterName())
            )
        );
    }

    public function createDaedalus(GameConfig $gameConfig, string $name, string $language): Daedalus
    {
        $this->entityManager->beginTransaction();

        try {
            $daedalus = new Daedalus();
            $daedalusConfig = $gameConfig->getDaedalusConfig();
            $daedalus
                ->setCycle(0)
                ->setDaedalusVariables($daedalusConfig);

            $localizationConfig = $this->localizationConfigRepository->findByLanguage($language);
            if ($localizationConfig === null) {
                throw new \Exception('there is no localizationConfig for this language');
            }

            $neron = new Neron();
            $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
            $daedalusInfo
                ->setName($name)
                ->setNeron($neron);
            $this->persistDaedalusInfo($daedalusInfo);

            $daedalus = $this->addTitlePrioritiesToDaedalus($daedalus);

            $daedalus = $this->setAvailableCharacters($daedalus);

            $daedalusEvent = new DaedalusInitEvent(
                $daedalus,
                $daedalusConfig,
                [EventEnum::CREATE_DAEDALUS],
                new \DateTime()
            );
            $this->eventService->callEvent($daedalusEvent, DaedalusInitEvent::NEW_DAEDALUS);
            $this->entityManager->commit();
        } catch (\Throwable $throwable) {
            $this->entityManager->rollback();
            $this->entityManager->close();

            throw $throwable;
        }

        return $daedalus;
    }

    public function endDaedalus(Daedalus $daedalus, string $cause, \DateTime $date): ClosedDaedalus
    {
        $this->killRemainingPlayers($daedalus, [$cause], $date);

        $daedalus->setFinishedAt(new \DateTime());

        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusInfo->setGameStatus(GameStatusEnum::FINISHED);

        $this->persistDaedalusInfo($daedalusInfo);

        // update closedDaedalus entity
        $closedDaedalus = $daedalusInfo->getClosedDaedalus();
        $closedDaedalus->updateEnd($daedalus, $cause);
        $daedalusInfo->setClosedDaedalus($closedDaedalus);
        $this->persistDaedalusInfo($daedalusInfo);

        /** @var Player $player */
        foreach ($daedalus->getPlayers() as $player) {
            /** @var ClosedPlayer $deadPlayerInfo */
            $deadPlayerInfo = $player->getPlayerInfo()->getClosedPlayer();

            $deadPlayerInfo->setClosedDaedalus($closedDaedalus);
            $closedDaedalus->addPlayer($deadPlayerInfo);

            $this->entityManager->persist($deadPlayerInfo);
            $this->entityManager->flush();
        }

        $this->entityManager->persist($closedDaedalus);
        $this->entityManager->flush();

        return $closedDaedalus;
    }

    public function closeDaedalus(Daedalus $daedalus, array $reasons, \DateTime $date): DaedalusInfo
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();

        $daedalusInfo->setGameStatus(GameStatusEnum::CLOSED);

        $daedalusEvent = new DaedalusEvent(
            $daedalus,
            $reasons,
            $date
        );
        $this->eventService->callEvent($daedalusEvent, DaedalusEvent::DELETE_DAEDALUS);

        $this->delete($daedalus);

        return $daedalusInfo;
    }

    public function startDaedalus(Daedalus $daedalus): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        $time = new \DateTime();
        $daedalus->setCreatedAt($time);
        $daedalus->setCycle($this->cycleService->getInDayCycleFromDate($time, $daedalus));
        $daedalus->setCycleStartedAt($this->cycleService->getDaedalusStartingCycleDate($daedalus));

        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);

        $this->persistDaedalusInfo($daedalusInfo);

        return $daedalus;
    }

    public function selectAlphaMush(Daedalus $daedalus, \DateTime $date): Daedalus
    {
        $gameConfig = $daedalus->getGameConfig();

        // Chose alpha Mushs
        $chancesArray = [];

        /** @var CharacterConfig $characterConfig */
        foreach ($daedalus->getAvailableCharacters() as $characterConfig) {
            if ($characterConfig
                ->getInitStatuses()
                ->map(static fn (StatusConfig $statusConfig) => $statusConfig->getStatusName())
                ->contains(PlayerStatusEnum::IMMUNIZED)
            ) {
                continue;
            }

            if ($daedalus->getPlayers()->getPlayerByName($characterConfig->getName())?->hasStatus(PlayerStatusEnum::BEGINNER)) {
                $mushChance = 1;
            } else {
                $mushChance = 2;
            }
            $chancesArray[$characterConfig->getCharacterName()] = $mushChance;
        }

        $mushNumber = $gameConfig->getDaedalusConfig()->getNbMush();

        $mushPlayerName = $this->randomService->getRandomElementsFromProbaCollection(new ProbaCollection($chancesArray), $mushNumber);
        foreach ($mushPlayerName as $playerName) {
            $mushPlayers = $daedalus
                ->getPlayers()
                ->filter(static fn (Player $player) => $player->getName() === $playerName);

            if (!$mushPlayers->isEmpty()) {
                /** @var Player $currentPlayer */
                $currentPlayer = $mushPlayers->first();
                $playerEvent = new PlayerEvent(
                    $currentPlayer,
                    [DaedalusEvent::FULL_DAEDALUS],
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

        if ($player === null) {
            return $daedalus;
        }

        if ($this->getOxygenCapsuleCount($player) === 0) {
            $this->playerService->killPlayer(player: $player, endReason: EndCauseEnum::ASPHYXIA, time: $date);
        } else {
            $capsule = $player->getEquipments()->filter(static fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->first();

            $equipmentEvent = new InteractWithEquipmentEvent(
                $capsule,
                $player,
                VisibilityEnum::PRIVATE,
                [EndCauseEnum::ASPHYXIA],
                $date
            );
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        return $daedalus;
    }

    public function killRemainingPlayers(Daedalus $daedalus, array $reasons, \DateTime $date): Daedalus
    {
        $playerAliveNb = $daedalus->getPlayers()->getPlayerAlive()->count();
        for ($i = 0; $i < $playerAliveNb; ++$i) {
            $player = $this->randomService->getAlivePlayerInDaedalus($daedalus);

            $endCause = EndCauseEnum::mapEndCause($reasons);
            $this->playerService->killPlayer(player: $player, endReason: $endCause, time: $date);
        }

        return $daedalus;
    }

    public function changeVariable(string $variableName, Daedalus $daedalus, int $change, \DateTime $date): Daedalus
    {
        $gameVariable = $daedalus->getVariableByName($variableName);

        $newVariableValuePoint = $gameVariable->getValue() + $change;
        $maxVariableValuePoint = $gameVariable->getMaxValue();
        $newVariableValuePoint = $this->getValueInInterval($newVariableValuePoint, 0, $maxVariableValuePoint);

        $daedalus->setVariableValueByName($newVariableValuePoint, $variableName);

        switch ($variableName) {
            case DaedalusVariableEnum::HULL:
                if ($newVariableValuePoint === 0) {
                    $daedalusEvent = new DaedalusEvent(
                        $daedalus,
                        [EndCauseEnum::DAEDALUS_DESTROYED],
                        $date
                    );

                    $this->eventService->callEvent($daedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
                }

                break;
        }

        $this->persist($daedalus);

        return $daedalus;
    }

    public function setVariable(string $variableName, Daedalus $daedalus, int $value, \DateTime $date): Daedalus
    {
        $gameVariable = $daedalus->getVariableByName($variableName);

        $maxVariableValuePoint = $gameVariable->getMaxValue();
        $value = $this->getValueInInterval($value, 0, $maxVariableValuePoint);

        $daedalus->setVariableValueByName($value, $variableName);

        if ($variableName === DaedalusVariableEnum::HULL && $value === 0) {
            $daedalusEvent = new DaedalusEvent(
                $daedalus,
                [EndCauseEnum::DAEDALUS_DESTROYED],
                $date
            );

            $this->eventService->callEvent($daedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
        }

        $this->persist($daedalus);

        return $daedalus;
    }

    public function findAllFinishedDaedaluses(): DaedalusCollection
    {
        return new DaedalusCollection($this->daedalusRepository->findFinishedDaedaluses());
    }

    public function findAllNonFinishedDaedaluses(): DaedalusCollection
    {
        return new DaedalusCollection($this->daedalusRepository->findNonFinishedDaedaluses());
    }

    public function findAllNonFinishedDaedalusesByLanguage(string $language): DaedalusCollection
    {
        return new DaedalusCollection($this->daedalusRepository->findNonFinishedDaedalusesByLanguage($language));
    }

    public function findAllDaedalusesOnCycleChange(): DaedalusCollection
    {
        return $this->daedalusRepository->findAllDaedalusesOnCycleChange();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function attributeTitles(Daedalus $daedalus, \DateTime $date): void
    {
        // Get the names of all alive players
        $players = $daedalus->getPlayers()->getPlayersEligibleForTitle();

        foreach ($daedalus->getTitlePriorities() as $titlePriority) {
            $titleAssigned = false;
            $title = $titlePriority->getName();

            foreach ($titlePriority->getPriority() as $priorityCharacter) {
                // This will return the player if it is alive, and null if not
                $player = $players->getPlayerByName($priorityCharacter);

                // if the player is not alive, continue to the next one
                if (!$player) {
                    continue;
                }

                if (!$titleAssigned && !$player->hasTitle($title)) {
                    // If first person in order of priority does not have title, assign it
                    $player->addTitle($title);
                    $playerEvent = new PlayerEvent(
                        $player,
                        [$title],
                        $date
                    );
                    $this->eventService->callEvent($playerEvent, PlayerEvent::TITLE_ATTRIBUTED);
                    $titleAssigned = true;
                } elseif (!$titleAssigned && $player->hasTitle($title)) {
                    $titleAssigned = true;
                } elseif ($titleAssigned && $player->hasTitle($title)) {
                    // If someone has a title when they are not the player alive with the biggest priority, remove it
                    // For when an inactive player wakes up
                    $player->removeTitle($title);
                    $playerEvent = new PlayerEvent(
                        $player,
                        [$title],
                        $date
                    );
                    $this->eventService->callEvent($playerEvent, PlayerEvent::TITLE_REMOVED);
                }
            }
        }

        // remove titles from inactive and mutated players
        $inactivePlayers = $daedalus->getAlivePlayers()->getPlayersIneligibleForTitle();
        foreach ($daedalus->getTitlePriorities() as $titlePriority) {
            foreach ($titlePriority->getPriority() as $playerName) {
                $player = $inactivePlayers->getPlayerByName($playerName);
                if (!$player) {
                    continue;
                }

                $player->removeTitle($titlePriority->getName());
                $playerEvent = new PlayerEvent($player, [$titlePriority->getName()], $date);
                $this->eventService->callEvent($playerEvent, PlayerEvent::TITLE_REMOVED);
            }
        }
    }

    /**
     * @deprecated do not call outside of DaedalusService and tests/cests
     */
    public function setAvailableCharacters(Daedalus $daedalus): Daedalus
    {
        $allCharacters = $daedalus->getGameConfig()->getCharactersConfig();
        $playerCount = $daedalus->getDaedalusConfig()->getPlayerCount();

        if ($daedalus->getDaedalusConfig()->getHoliday() === HolidayEnum::APRIL_FOOLS) {
            $randomCharacters = $this->randomService->getRandomElements($allCharacters->toArray(), $playerCount);
            $daedalus->setAvailableCharacters(new CharacterConfigCollection());
            foreach ($randomCharacters as $randomCharacter) {
                $daedalus->addAvailableCharacter($randomCharacter);
            }
        } else {
            $allCharacters = $this->handleChaolaToggle($allCharacters, $daedalus);
            while ($playerCount < $allCharacters->count()) {
                // This should never trigger until playerCount is something different than 16, so doesn't matter for now. this is a failsafe for curious people messing with PlayerCount
                $randomCharacter = $this->randomService->getRandomElement($allCharacters->toArray());
                $allCharacters->removeElement($randomCharacter);
            }
            $daedalus->setAvailableCharacters($allCharacters);
        }

        return $daedalus;
    }

    private function getRandomPlayersWithLessOxygen(Daedalus $daedalus): ?Player
    {
        $playersAlive = $daedalus->getPlayers()->getPlayerAlive();

        if ($playersAlive->isEmpty()) {
            return null;
        }

        $playersWithLessOxygen = new PlayerCollection();
        $lessOxygenCount = 0;

        foreach ($playersAlive as $player) {
            $playerOxygenCount = $this->getOxygenCapsuleCount($player);

            if ($playersWithLessOxygen->isEmpty()) {
                $playersWithLessOxygen->add($player);
                $lessOxygenCount = $playerOxygenCount;
            } elseif ($playerOxygenCount === $lessOxygenCount) {
                $playersWithLessOxygen->add($player);
            } elseif ($playerOxygenCount < $lessOxygenCount) {
                $playersWithLessOxygen = new PlayerCollection([$player]);
                $lessOxygenCount = $playerOxygenCount;
            }
        }

        return $this->randomService->getRandomPlayer($playersWithLessOxygen);
    }

    private function getOxygenCapsuleCount(Player $player): int
    {
        return $player->getEquipments()->filter(static fn (GameItem $item) => $item->getName() === ItemEnum::OXYGEN_CAPSULE)->count();
    }

    private function getValueInInterval(int $value, ?int $min, ?int $max): int
    {
        if ($max !== null && $value > $max) {
            return $max;
        }
        if ($min !== null && $value < $min) {
            return $min;
        }

        return $value;
    }

    private function addTitlePrioritiesToDaedalus(Daedalus $daedalus): Daedalus
    {
        foreach ($daedalus->getGameConfig()->getTitleConfigs() as $titleConfig) {
            $titlePriority = new TitlePriority($titleConfig, $daedalus);
            $this->titlePriorityRepository->save($titlePriority);
            $daedalus->addTitlePriority($titlePriority);
        }

        return $daedalus;
    }

    private function handleChaolaToggle(CharacterConfigCollection $characterCollection, Daedalus $daedalus): CharacterConfigCollection
    {
        $chaolaToggle = $daedalus->getDaedalusConfig()->getChaolaToggle();

        $charactersToRemove = match ($chaolaToggle) {
            CharacterSetEnum::ALL => [],
            CharacterSetEnum::NONE => CharacterEnum::allPairs(),
            CharacterSetEnum::ANDIE_DEREK => CharacterEnum::chaolaPair(),
            CharacterSetEnum::FINOLA_CHAO => CharacterEnum::andrekPair(),
            CharacterSetEnum::ONE => $this->randomService->isSuccessful(50) ? CharacterEnum::andrekPair() : CharacterEnum::chaolaPair(),
            CharacterSetEnum::RANDOM => $this->randomService->getRandomElements(CharacterEnum::allPairs(), 2),
            default => throw new \RuntimeException("Invalid value for chaolaToggle: {$chaolaToggle}"),
        };

        return $characterCollection->getAllExcept($charactersToRemove);
    }
}
