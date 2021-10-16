<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TriumphEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Repository\DeadPlayerInfoRepository;
use Mush\Player\Repository\PlayerRepository;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\ChargeStatusEvent;
use Mush\Status\Event\StatusEvent;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerService implements PlayerServiceInterface
{
    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $eventDispatcher;

    private PlayerRepository $repository;

    private DeadPlayerInfoRepository $deadPlayerRepository;

    private RoomLogServiceInterface $roomLogService;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PlayerRepository $repository,
        DeadPlayerInfoRepository $deadPlayerRepository,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->deadPlayerRepository = $deadPlayerRepository;
        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
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

    public function findDeadPlayerInfo(Player $player): ?DeadPlayerInfo
    {
        return $this->deadPlayerRepository->findOneByPlayer($player);
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
            $statusEvent = new StatusEvent($statusName, $player, PlayerEvent::NEW_PLAYER, new \DateTime());

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
        }

        $this->persist($player);

        if (!(in_array(PlayerStatusEnum::IMMUNIZED, $characterConfig->getStatuses()))) {
            $statusEvent = new ChargeStatusEvent(PlayerStatusEnum::SPORES, $player, PlayerEvent::NEW_PLAYER, new \DateTime());

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
        }

        $user->setCurrentGame($player);
        $playerEvent = new PlayerEvent(
            $player,
            EventEnum::CREATE_DAEDALUS,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::NEW_PLAYER);

        return $player;
    }

    public function endPlayer(Player $player, string $message): Player
    {
        $deadPlayerInfo = $this->findDeadPlayerInfo($player);
        if ($deadPlayerInfo === null) {
            throw new \LogicException('unable to find deadPlayerInfo');
        }

        $deadPlayerInfo->setMessage($message);

        $player->setGameStatus(GameStatusEnum::CLOSED);

        $playerEvent = new PlayerEvent(
            $player,
            PlayerEvent::END_PLAYER,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::END_PLAYER);

        $this->entityManager->persist($deadPlayerInfo);
        $this->persist($player);

        return $player;
    }

    public function handleNewCycle(Player $player, \DateTime $date): Player
    {
        if (!$player->isAlive()) {
            return $player;
        }

        if ($player->getMoralPoint() === 0) {
            $playerEvent = new PlayerEvent(
                $player,
                EndCauseEnum::DEPRESSION,
                $date
            );
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);

            return $player;
        }

        $playerModifierEvent = new PlayerModifierEvent(
            $player,
            1,
            EventEnum::NEW_CYCLE,
            $date);
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::ACTION_POINT_MODIFIER);

        $playerModifierEvent = new PlayerModifierEvent(
            $player,
            1,
            EventEnum::NEW_CYCLE,
            $date
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MOVEMENT_POINT_MODIFIER);

        $playerModifierEvent = new PlayerModifierEvent(
            $player,
            -1,
            EventEnum::NEW_CYCLE,
            $date
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);

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

        $this->roomLogService->createLog(
            LogEnum::GAIN_TRIUMPH,
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            ['quantity' => $triumphChange],
            $date
        );

        return $this->persist($player);
    }

    public function handleNewDay(Player $player, \DateTime $date): Player
    {
        if (!$player->isAlive()) {
            return $player;
        }

        $playerModifierEvent = new PlayerModifierEvent(
            $player,
            1,
            EventEnum::NEW_DAY,
            $date
        );
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);

        if (!$player->isMush()) {
            $playerModifierEvent = new PlayerModifierEvent(
                $player,
                -2,
                EventEnum::NEW_DAY,
                $date
            );
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        }

        return $this->persist($player);
    }

    public function playerDeath(Player $player, ?string $reason, \DateTime $time): Player
    {
        if (!$reason) {
            $reason = 'missing end reason';
        }

        $deadPlayerInfo = new DeadPlayerInfo();
        $deadPlayerInfo
            ->setPlayer($player)
            ->setDayDeath($player->getDaedalus()->getDay())
            ->setCycleDeath($player->getDaedalus()->getCycle())
            ->setEndStatus($reason)
        ;

        $this->entityManager->persist($deadPlayerInfo);

        if ($reason !== EndCauseEnum::DEPRESSION) {
            /** @var Player $daedalusPlayer */
            foreach ($player->getDaedalus()->getPlayers()->getPlayerAlive() as $daedalusPlayer) {
                if ($daedalusPlayer !== $player) {
                    $playerModifierEvent = new PlayerModifierEvent(
                        $daedalusPlayer,
                        -1,
                        EventEnum::PLAYER_DEATH,
                        $time
                    );
                    $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
                }
            }
        }

        $currentRoom = $player->getPlace();
        foreach ($player->getItems() as $item) {
            $item->setPlayer(null);
            $item->setPlace($currentRoom);
            $this->gameEquipmentService->persist($item);
        }

        /** @var Status $status */
        foreach ($player->getStatuses() as $status) {
            if ($status->getName() !== PlayerStatusEnum::MUSH) {
                $player->removeStatus($status);
            }
        }

        //@TODO in case of assassination chance of disorder for roommates

        $player->setGameStatus(GameStatusEnum::FINISHED);

        $this->persist($player);

        return $player;
    }
}
