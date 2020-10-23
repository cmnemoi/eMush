<?php

namespace Mush\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\Collection\CharacterConfigCollection;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CharacterConfigServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\GameStatusEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Repository\PlayerRepository;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlayerService implements PlayerServiceInterface
{
    private EntityManagerInterface $entityManager;

    private EventDispatcherInterface $eventDispatcher;

    private PlayerRepository $repository;

    private GameConfig $gameConfig;

    private CharacterConfigCollection $charactersConfig;

    private TokenStorageInterface $tokenStorage;

    /**
     * PlayerService constructor.
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param PlayerRepository $repository
     * @param GameConfigServiceInterface $gameConfigService
     * @param CharacterConfigServiceInterface $characterConfigsService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        PlayerRepository $repository,
        GameConfigServiceInterface $gameConfigService,
        CharacterConfigServiceInterface $characterConfigsService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->charactersConfig = $characterConfigsService->getConfigs();
        $this->tokenStorage = $tokenStorage;
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

    public function findUserCurrentGame(User $user): ?Player
    {
        return $this->repository->findOneBy(['user' => $user, 'gameStatus' => GameStatusEnum::CURRENT]);
    }

    public function createPlayer(Daedalus $daedalus, string $character): Player
    {
        $player = new Player();

        $player
            ->setUser($this->tokenStorage->getToken()->getUser())
            ->setGameStatus(GameStatusEnum::CURRENT)
            ->setDaedalus($daedalus)
            ->setRoom(
                $daedalus->getRooms()
                    ->filter(fn (Room $room) => $room->getName() === RoomEnum::LABORATORY)
                    ->first()
            )
            ->setPerson($character)
            ->setSkills([])
            ->setHealthPoint($this->gameConfig->getInitHealthPoint())
            ->setMoralPoint($this->gameConfig->getInitMoralPoint())
            ->setActionPoint($this->gameConfig->getInitActionPoint())
            ->setMovementPoint($this->gameConfig->getInitMovementPoint())
            ->setSatiety($this->gameConfig->getInitSatiety())
            ->setStatuses($this->charactersConfig->getCharacter($character)->getStatuses())
        ;

        $playerEvent = new PlayerEvent($player);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::NEW_PLAYER);

        return $this->persist($player);
    }
}
