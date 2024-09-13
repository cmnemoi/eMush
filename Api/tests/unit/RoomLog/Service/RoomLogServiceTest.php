<?php

namespace Mush\Tests\unit\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\RoomLog\Service\RoomLogService;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RoomLogServiceTest extends TestCase
{
    private EntityManagerInterface|Mockery\Mock $entityManager;
    private Mockery\Mock|RandomServiceInterface $randomService;
    private Mockery\Mock|RoomLogRepository $repository;
    private Mockery\Mock|TranslationServiceInterface $translationService;
    private RoomLogService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->repository = \Mockery::mock(RoomLogRepository::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->service = new RoomLogService(
            $this->entityManager,
            $this->randomService,
            $this->repository,
            $this->translationService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testFindById()
    {
        $log = new RoomLog();
        $this->repository->shouldReceive('find')->with(5)->andReturn($log)->once();

        self::assertSame($log, $this->service->findById(5));
    }

    public function testPersist()
    {
        $log = new RoomLog();
        $this->entityManager->shouldReceive('persist')->with($log)->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->service->persist($log);
    }

    public function testCreateSimpleLog()
    {
        $daedalusInfo = new DaedalusInfo(new Daedalus(), new GameConfig(), new LocalizationConfig());
        $daedalus = $daedalusInfo->getDaedalus();

        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');
        $visibility = VisibilityEnum::PUBLIC;
        $type = 'log';
        $player = null;
        $parameters = [];
        $dateTime = new \DateTime();

        $this->entityManager->shouldReceive('flush')->once();
        $this->entityManager->shouldReceive('persist')->once();

        $test = $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        self::assertSame($logKey, $test->getLog());
        self::assertSame([], $test->getParameters());
        self::assertSame('log', $test->getType());
        self::assertSame($place->getName(), $test->getPlace());
        self::assertSame($player, $test->getPlayerInfo());
        self::assertSame($visibility, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateLogWithParameters()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');
        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setCharacterName('gioele');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');
        $visibility = VisibilityEnum::PUBLIC;
        $type = 'log';
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place);

        $parameters = ['character' => 'andie', 'quantity' => 5, 'target_character' => 'gioele'];
        $dateTime = new \DateTime();

        $this->entityManager->shouldReceive('flush')->once();
        $this->entityManager->shouldReceive('persist')->once();

        $test = $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        self::assertSame($logKey, $test->getLog());
        self::assertSame($parameters, $test->getParameters());
        self::assertSame('log', $test->getType());
        self::assertSame($place->getName(), $test->getPlace());
        self::assertSame($playerInfo, $test->getPlayerInfo());
        self::assertSame($visibility, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateSecretLog()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');
        $visibility = VisibilityEnum::SECRET;
        $type = 'log';
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place);

        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $this->entityManager->shouldReceive('flush')->once();
        $this->entityManager->shouldReceive('persist')->once();

        $test = $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        self::assertSame($logKey, $test->getLog());
        self::assertSame($parameters, $test->getParameters());
        self::assertSame('log', $test->getType());
        self::assertSame($place->getName(), $test->getPlace());
        self::assertSame($playerInfo, $test->getPlayerInfo());
        self::assertSame($visibility, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateSecretRevealedLog()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setCharacterName('chao');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');
        $visibility = VisibilityEnum::SECRET;
        $type = 'log';
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place);

        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $player2 = new Player();
        $playerInfo2 = new PlayerInfo($player2, new User(), $characterConfig2);
        $player2
            ->setPlayerInfo($playerInfo2)
            ->setPlace($place);
        (new \ReflectionProperty($player2, 'id'))->setValue($player2, 2);

        $this->entityManager->shouldReceive('flush')->once();

        $this->entityManager->shouldReceive('persist')->once();

        $test = $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        self::assertSame($logKey, $test->getLog());
        self::assertSame($parameters, $test->getParameters());
        self::assertSame('log', $test->getType());
        self::assertSame($place->getName(), $test->getPlace());
        self::assertSame($playerInfo, $test->getPlayerInfo());
        self::assertSame(VisibilityEnum::REVEALED, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateCovertRevealedLog()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');
        $visibility = VisibilityEnum::COVERT;
        $type = 'log';
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place);

        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $cameraEquipment = new GameEquipment($place);
        $cameraEquipment->setName(EquipmentEnum::CAMERA_EQUIPMENT);

        $this->entityManager->shouldReceive('flush')->once();

        $this->entityManager->shouldReceive('persist')->once();

        $test = $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        self::assertSame($logKey, $test->getLog());
        self::assertSame($parameters, $test->getParameters());
        self::assertSame('log', $test->getType());
        self::assertSame($place->getName(), $test->getPlace());
        self::assertSame($playerInfo, $test->getPlayerInfo());
        self::assertSame(VisibilityEnum::REVEALED, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateCovertItemCameraLog()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');
        $visibility = VisibilityEnum::COVERT;
        $type = 'log';
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place);
        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $cameraEquipment = new GameItem($place);
        $cameraEquipment->setName(ItemEnum::CAMERA_ITEM);

        $this->entityManager->shouldReceive('flush')->once();
        $this->entityManager->shouldReceive('persist')->once();

        $test = $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        self::assertSame($logKey, $test->getLog());
        self::assertSame($parameters, $test->getParameters());
        self::assertSame('log', $test->getType());
        self::assertSame($place->getName(), $test->getPlace());
        self::assertSame($playerInfo, $test->getPlayerInfo());
        self::assertSame($visibility, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateActionSuccessLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);
        $actionResult = new Success();
        $actionResult->setVisibility(VisibilityEnum::PUBLIC);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $actionEvent = new ActionEvent(
            actionConfig: ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::STRENGTHEN_HULL)),
            actionProvider: $this->createStub(ActionProviderInterface::class),
            player: $player,
            tags: []
        );
        $actionEvent->setActionResult($actionResult);

        $test = $this->service->createLogFromActionEvent($actionEvent);

        self::assertSame(ActionLogEnum::STRENGTHEN_SUCCESS, $test->getLog());
        self::assertSame(['character' => 'andie'], $test->getParameters());
        self::assertSame('actions_log', $test->getType());
        self::assertSame($player->getPlace()->getName(), $test->getPlace());
        self::assertSame($player->getPlayerInfo(), $test->getPlayerInfo());
        self::assertSame(VisibilityEnum::PUBLIC, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateActionFailLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        $actionResult = new Fail();
        $actionResult->setVisibility(VisibilityEnum::PRIVATE);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $actionEvent = new ActionEvent(
            actionConfig: ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::STRENGTHEN_HULL)),
            actionProvider: $this->createStub(ActionProviderInterface::class),
            player: $player,
            tags: []
        );
        $actionEvent->setActionResult($actionResult);

        $test = $this->service->createLogFromActionEvent($actionEvent);

        self::assertSame(ActionLogEnum::DEFAULT_FAIL, $test->getLog());
        self::assertSame(['character' => 'andie'], $test->getParameters());
        self::assertSame('actions_log', $test->getType());
        self::assertSame($player->getPlace()->getName(), $test->getPlace());
        self::assertSame($player->getPlayerInfo(), $test->getPlayerInfo());
        self::assertSame(VisibilityEnum::PRIVATE, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testCreateActionWithParameterLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        $equipmentConfig = new EquipmentConfig();
        $gameEquipment = new GameEquipment(new Place());
        $gameEquipment->setName('equipment')->setEquipment($equipmentConfig);

        $actionResult = new Fail($gameEquipment);
        $actionResult->setVisibility(VisibilityEnum::PRIVATE);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $actionEvent = new ActionEvent(
            actionConfig: ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::STRENGTHEN_HULL)),
            actionProvider: $this->createStub(ActionProviderInterface::class),
            player: $player,
            tags: [],
            actionTarget: $gameEquipment
        );
        $actionEvent->setActionResult($actionResult);

        $test = $this->service->createLogFromActionEvent($actionEvent);

        self::assertSame(ActionLogEnum::DEFAULT_FAIL, $test->getLog());
        self::assertSame(['character' => 'andie', 'target_equipment' => 'equipment'], $test->getParameters());
        self::assertSame('actions_log', $test->getType());
        self::assertSame($player->getPlace()->getName(), $test->getPlace());
        self::assertSame($player->getPlayerInfo(), $test->getPlayerInfo());
        self::assertSame(VisibilityEnum::PRIVATE, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testGetLogs()
    {
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $gameConfig = new GameConfig();

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $player->setPlace($place)->setDaedalus($daedalus);

        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $date = new \DateTime();

        $roomLog1 = new RoomLog();
        $roomLog1
            ->setLog('logKey1')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCreatedAt($date)
            ->setParameters([])
            ->setDay(1)
            ->setCycle(3)
            ->setType('log');

        $roomLog2 = new RoomLog();
        $roomLog2
            ->setLog('logKey2')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCreatedAt($date)
            ->setParameters(['player' => 'andie'])
            ->setDay(1)
            ->setCycle(4)
            ->setType('log');

        $this->repository
            ->shouldReceive('getPlayerRoomLog')
            ->once()
            ->andReturn([$roomLog1, $roomLog2]);

        $logs = $this->service->getRoomLog($player);
        $expectedLogs = new RoomLogCollection([$roomLog1, $roomLog2]);

        self::assertEquals($expectedLogs, $logs);
    }

    public function testCreateSecretLogDeadPlayerInRoom()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setCharacterName('chao');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');
        $visibility = VisibilityEnum::SECRET;
        $type = 'log';
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place);

        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $player2 = new Player();
        $playerInfo2 = new PlayerInfo($player2, new User(), $characterConfig2);
        $playerInfo2->setGameStatus(GameStatusEnum::CLOSED);
        $player2
            ->setPlayerInfo($playerInfo2)
            ->setPlace($place);

        $this->entityManager->shouldReceive('flush')->once();

        $this->entityManager->shouldReceive('persist')->once();

        $test = $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        self::assertSame($logKey, $test->getLog());
        self::assertSame($parameters, $test->getParameters());
        self::assertSame('log', $test->getType());
        self::assertSame($place->getName(), $test->getPlace());
        self::assertSame($playerInfo, $test->getPlayerInfo());
        self::assertSame(VisibilityEnum::SECRET, $test->getVisibility());
        self::assertSame(4, $test->getCycle());
        self::assertSame(2, $test->getDay());
    }

    public function testMarkAllRoomLogsAsReadForPlayer()
    {
        // given a player
        $player = new Player();
        (new \ReflectionProperty($player, 'id'))->setValue($player, 1);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        // given some unread room logs
        $roomLogs = $this->roomLogFactory($playerInfo, number: 5);

        // given two of them are already read
        $roomLogs->get(0)->addReader($player);
        $roomLogs->get(1)->addReader($player);

        // given universe is setup so that everything works
        $this->repository
            ->shouldReceive('getPlayerRoomLog')
            ->once()
            ->andReturn($roomLogs->toArray());
        $this->entityManager->shouldReceive('beginTransaction')->once();
        $this->entityManager->shouldReceive('persist')->times(3);
        $this->entityManager->shouldReceive('flush')->once();
        $this->entityManager->shouldReceive('commit')->once();

        // when I call markAllRoomLogsAsReadForPlayer
        $this->service->markAllRoomLogsAsReadForPlayer($player);

        // then all player room logs should be marked as read
        $roomLogs->map(function (RoomLog $roomLog) use ($player) {
            $this->assertTrue($roomLog->isReadBy($player));
        });
    }

    public function testObservantRevealsCovertLog(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);
        $observant = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);
        new Skill(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::OBSERVANT)), $observant);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->randomService->shouldReceive('isSuccessful')->with(RoomLogService::OBSERVANT_REVEAL_CHANCE)->andReturn(true)->once();

        $roomLog = $this->service->createLog(
            ActionLogEnum::MAKE_SICK,
            $player->getPlace(),
            VisibilityEnum::COVERT,
            'log',
            $player,
            [],
            new \DateTime()
        );

        self::assertSame(VisibilityEnum::REVEALED, $roomLog->getVisibility());
    }

    private function roomLogFactory(PlayerInfo $playerInfo, int $number = 1): RoomLogCollection
    {
        $roomLogs = new RoomLogCollection();

        for ($i = 0; $i < $number; ++$i) {
            $roomLog = new RoomLog();
            $roomLog
                ->setPlayerInfo($playerInfo)
                ->setLog('logKey')
                ->setVisibility(VisibilityEnum::PUBLIC)
                ->setCreatedAt(new \DateTime())
                ->setParameters([])
                ->setDay(1)
                ->setCycle(1)
                ->setType('log');

            $roomLogs->add($roomLog);
        }

        return $roomLogs;
    }
}
