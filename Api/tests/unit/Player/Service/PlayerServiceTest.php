<?php

namespace Mush\Tests\unit\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\InMemoryDaedalusRepository;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryClosedPlayerRepository;
use Mush\Player\Repository\InMemoryPlayerInfoRepository;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Player\Service\PlayerService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerServiceTest extends TestCase
{
    private InMemoryClosedPlayerRepository $closedPlayerRepository;

    private InMemoryDaedalusRepository $daedalusRepository;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    private InMemoryPlayerRepository $playerRepository;

    /** @var Mockery\Mock|RoomLogServiceInterface */
    private RoomLogServiceInterface $roomLogService;

    private InMemoryPlayerInfoRepository $playerInfoRepository;

    private CharacterConfigCollection $charactersConfigs;

    private PlayerService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->closedPlayerRepository = new InMemoryClosedPlayerRepository();
        $this->daedalusRepository = new InMemoryDaedalusRepository();
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->daedalusRepository = new InMemoryDaedalusRepository();
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->roomLogService = \Mockery::mock(RoomLogServiceInterface::class);
        $this->playerInfoRepository = new InMemoryPlayerInfoRepository();

        $this->charactersConfigs = new CharacterConfigCollection();

        $this->service = new PlayerService(
            closedPlayerRepository: $this->closedPlayerRepository,
            daedalusRepository: $this->daedalusRepository,
            eventService: $this->eventService,
            playerRepository: $this->playerRepository,
            roomLogService: $this->roomLogService,
            playerInfoRepository: $this->playerInfoRepository,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        $this->closedPlayerRepository->clear();
        $this->daedalusRepository->clear();
        $this->playerRepository->clear();
        $this->playerInfoRepository->clear();
        \Mockery::close();
    }

    public function testCreatePlayer()
    {
        $user = new User();
        $gameConfig = new GameConfig();

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $this->daedalusRepository->save($daedalus);

        $laboratory = new Place();
        $laboratory->setName(RoomEnum::LABORATORY); // @FIXME: should we move the starting room in the config
        $daedalus->addPlace($laboratory);

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName('some status');

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setCharacterName('character')
            ->setInitStatuses(new ArrayCollection([$statusConfig]))
            ->setInitMovementPoint(0)
            ->setInitActionPoint(1)
            ->setInitSatiety(2)
            ->setInitMoralPoint(3)
            ->setInitHealthPoint(4);
        $this->charactersConfigs->add($characterConfig);

        $gameConfig->setCharactersConfig($this->charactersConfigs);

        $this->eventService->shouldReceive('callEvent')->once();

        $this->service->createPlayer($daedalus, $user, 'character');

        $savedPlayer = $this->playerRepository->findOneByName('character');

        self::assertInstanceOf(Player::class, $savedPlayer);
        self::assertSame('character', $savedPlayer->getPlayerInfo()->getCharacterConfig()->getCharacterName());
        self::assertSame($characterConfig->getInitActionPoint(), $savedPlayer->getActionPoint());
        self::assertSame($characterConfig->getInitMovementPoint(), $savedPlayer->getMovementPoint());
        self::assertSame($characterConfig->getInitHealthPoint(), $savedPlayer->getHealthPoint());
        self::assertSame($characterConfig->getInitMoralPoint(), $savedPlayer->getMoralPoint());
        self::assertSame($characterConfig->getInitSatiety(), $savedPlayer->getSatiety());
        self::assertCount(0, $savedPlayer->getEquipments());
        self::assertCount(0, $savedPlayer->getSkills());
    }

    public function testkillPlayer()
    {
        $gameConfig = new GameConfig();
        $room = new Place();
        $room->setType(PlaceTypeEnum::ROOM)->setName('randomRoom');

        $daedalus = new Daedalus();
        $daedalus
            ->setCycle(3)
            ->setDay(5)
            ->addPlace($room);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $this->daedalusRepository->save($daedalus);

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('name');

        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->setPlace($room);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);
        $player->setPlayerInfo($playerInfo);
        $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();
        (new \ReflectionClass($closedPlayer))->getProperty('id')->setValue($closedPlayer, random_int(1, PHP_INT_MAX));

        $this->playerRepository->save($player);

        $gameItem = new GameItem($player);

        $closedPlayer = $playerInfo->getClosedPlayer();
        $this->closedPlayerRepository->save($closedPlayer);

        $this->eventService->shouldReceive('callEvent')->once();

        $reason = 'bled';

        $this->service->killPlayer($player, $reason, new \DateTime());

        $savedPlayer = $this->playerRepository->findOneByName('name');

        self::assertTrue($savedPlayer->isDead());
    }

    public function testEndPlayer()
    {
        $user = new User();

        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $player->setPlayerInfo($playerInfo);
        $this->playerRepository->save($player);

        $message = 'message';

        $this->eventService->shouldReceive('callEvent');

        $this->service->endPlayer($player, $message, []);

        $savedPlayer = $this->playerRepository->findOneByName('name');

        self::assertSame(GameStatusEnum::CLOSED, $savedPlayer->getPlayerInfo()->getGameStatus());
        self::assertSame($savedPlayer->getPlayerInfo()->getClosedPlayer()->getMessage(), $message);
    }

    public function testShouldKillPlayerAtZeroMoraleAtCycleChange(): void
    {
        $player = $this->givenAndieWithMorale(0);
        $this->playerRepository->save($player);

        $this->whenIHandleNewCycleForPlayer($player);

        $savedPlayer = $this->playerRepository->findOneByName(CharacterEnum::ANDIE);

        $this->thenPlayerShouldBeDead($savedPlayer);
    }

    private function givenAndieWithMorale(int $morale): Player
    {
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, DaedalusFactory::createDaedalus());
        $player->setMoralPoint($morale);

        return $player;
    }

    private function whenIHandleNewCycleForPlayer(Player $player): void
    {
        $this->eventService->shouldIgnoreMissing();

        $this->service->handleNewCycle($player, new \DateTime());
    }

    private function thenPlayerShouldBeDead(Player $player): void
    {
        self::assertTrue($player->isDead());
    }
}
