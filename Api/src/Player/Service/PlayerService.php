<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TriumphEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Repository\PlayerRepository;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerService implements PlayerServiceInterface
{
    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $eventDispatcher;

    private PlayerRepository $repository;

    private RoomLogServiceInterface $roomLogService;

    private StatusServiceInterface $statusService;

    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PlayerRepository $repository,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
        $this->randomService = $randomService;
    }

    public function persist(Player $player): Player
    {
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $player;
    }

    public function findById(int $id): ?Player
    {
        return $this->repository->find($id);
    }

    public function findOneByCharacter(string $character, Daedalus $daedalus): ?Player
    {
        return $this->repository->findOneByName($character, $daedalus);
    }

    public function findUserCurrentGame(User $user): ?Player
    {
        return $this->repository->findOneBy(['user' => $user, 'gameStatus' => GameStatusEnum::CURRENT]);
    }

    public function createPlayer(Daedalus $daedalus, User $user, string $character): Player
    {
        $player = new Player();

        $gameConfig = $daedalus->getGameConfig();

        $characterConfig = $gameConfig->getCharactersConfig()->getCharacter($character);
        if (!$characterConfig) {
            throw new \LogicException('Character not available');
        }

        $player
            ->setUser($user)
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setDaedalus($daedalus)
            ->setPlace(
                $daedalus->getRooms()
                    ->filter(fn (Place $room) => RoomEnum::LABORATORY === $room->getName())
                    ->first()
            )
            ->setCharacterConfig($characterConfig)
            ->setSkills([])
            ->setHealthPoint($gameConfig->getInitHealthPoint())
            ->setMoralPoint($gameConfig->getInitMoralPoint())
            ->setActionPoint($gameConfig->getInitActionPoint())
            ->setMovementPoint($gameConfig->getInitMovementPoint())
            ->setSatiety($gameConfig->getInitSatiety())
            ->setSatiety($gameConfig->getInitSatiety())
        ;

        foreach ($characterConfig->getStatuses() as $statusName) {
            $this->statusService->createCoreStatus($statusName, $player);
        }

        if (!(in_array(PlayerStatusEnum::IMMUNIZED, $characterConfig->getStatuses()))) {
            $this->statusService->createSporeStatus($player);
        }

        $this->persist($player);

        $user->setCurrentGame($player);
        $playerEvent = new PlayerEvent($player);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::NEW_PLAYER);

        return $player;
    }

    public function handleNewCycle(Player $player, \DateTime $date): Player
    {
        if (!$player->isAlive()) {
            return $player;
        }

        if ($player->getMoralPoint() === 0) {
            $playerEvent = new PlayerEvent($player, $date);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

            return $player;
        }

        $actionModifier = new Modifier();
        $actionModifier
            ->setDelta(1)
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
        ;
        $playerEvent = new PlayerEvent($player, $date);
        $playerEvent->setModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $movementModifier = new Modifier();
        $movementModifier
            ->setDelta(1)
            ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
        ;
        $playerEvent->setModifier($movementModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $satietyModifier = new Modifier();
        $satietyModifier
            ->setDelta(-1)
            ->setTarget(ModifierTargetEnum::SATIETY)
        ;
        $playerEvent->setModifier($satietyModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $triumphChange = 0;

        $gameConfig = $player->getDaedalus()->getGameConfig();

        if ($player->isMush() &&
            ($mushTriumph = $gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_MUSH))
        ) {
            $triumphChange = $mushTriumph->getTriumph();
        }

        if (!$player->isMush() &&
            ($humanTriumph = $gameConfig->getTriumphConfig()->getTriumph(TriumphEnum::CYCLE_HUMAN))
        ) {
            $triumphChange = $humanTriumph->getTriumph();
        }

        $player->addTriumph($triumphChange);

        $this->roomLogService->createQuantityLog(
            LogEnum::GAIN_TRIUMPH,
            $player->getPlace(),
            $player,
            VisibilityEnum::PRIVATE,
            $triumphChange,
            $date
        );

        //Metal Plates
        if ($this->randomService->isSuccessful($gameConfig->getDifficultyConfig()->getMetalPlateRate())) {
            $playerEvent = new PlayerEvent($player, $date);
            $playerEvent->setReason(EndCauseEnum::METAL_PLATE);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::METAL_PLATE);
        }

        //Panic Crisis
        if ($this->randomService->isSuccessful($gameConfig->getDifficultyConfig()->getPanicCrisisRate()) &&
            !$player->isMush()
        ) {
            $playerEvent = new PlayerEvent($player, $date);
            $playerEvent->setReason(EndCauseEnum::DEPRESSION);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::PANIC_CRISIS);
        }

        return $this->persist($player);
    }

    public function handleNewDay(Player $player, \DateTime $date): Player
    {
        if (!$player->isAlive()) {
            return $player;
        }

        $playerEvent = new PlayerEvent($player, $date);

        $healthModifier = new Modifier();
        $healthModifier
            ->setDelta(1)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT)
        ;
        $playerEvent->setModifier($healthModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $moralModifier = new Modifier();
        $moralModifier
            ->setDelta(-2)
            ->setTarget(ModifierTargetEnum::MORAL_POINT)
        ;

        $playerEvent->setModifier($moralModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        return $this->persist($player);
    }

    public function playerDeath(Player $player, ?string $reason, \DateTime $time): Player
    {
        if ($reason) {
            $player->setEndStatus($reason);
        }

        if ($player->getEndStatus() !== EndCauseEnum::DEPRESSION) {
            /** @var Player $daedalusPlayer */
            foreach ($player->getDaedalus()->getPlayers()->getPlayerAlive() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player) {
                    $actionModifier = new Modifier();
                    $actionModifier
                        ->setDelta(-1)
                        ->setTarget(ModifierTargetEnum::MORAL_POINT)
                    ;
                    $playerEvent = new PlayerEvent($daedalusPlayer, $time);
                    $playerEvent->setModifier($actionModifier);

                    $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
                }
            }
        }

        foreach ($player->getItems() as $item) {
            $item->setPlayer(null);
            $item->setPlace($player->getPlace());
        }

        foreach ($player->getStatuses() as $status) {
            $player->removeStatus($status);
        }

        //@TODO in case of assasination chance of disorder for roommates
        if ($grandBeyond = $player->getDaedalus()->getPlaceByName(RoomEnum::GREAT_BEYOND)) {
            $player->setPlace($grandBeyond);
        }

        //@TODO two steps death
        $player->setGameStatus(GameStatusEnum::FINISHED);

        return $player;
    }
}
