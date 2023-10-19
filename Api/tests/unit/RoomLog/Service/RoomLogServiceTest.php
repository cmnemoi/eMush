<?php

namespace Mush\Tests\unit\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\RoomLog\Service\RoomLogService;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class RoomLogServiceTest extends TestCase
{
    private EntityManagerInterface|Mockery\Mock $entityManager;

    private RandomServiceInterface|Mockery\Mock $randomService;

    private RoomLogRepository|Mockery\Mock $repository;

    private TranslationServiceInterface|Mockery\Mock $translationService;

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

        $this->assertEquals($log, $this->service->findById(5));
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

        $this->assertEquals($logKey, $test->getLog());
        $this->assertEquals([], $test->getParameters());
        $this->assertEquals('log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($player, $test->getPlayerInfo());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
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
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

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

        $this->assertEquals($logKey, $test->getLog());
        $this->assertEquals($parameters, $test->getParameters());
        $this->assertEquals('log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
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
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

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

        $this->assertEquals($logKey, $test->getLog());
        $this->assertEquals($parameters, $test->getParameters());
        $this->assertEquals('log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
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
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $player2 = new Player();
        $playerInfo2 = new PlayerInfo($player2, new User(), $characterConfig2);
        $player2
            ->setPlayerInfo($playerInfo2)
            ->setPlace($place)
        ;

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

        $this->assertEquals($logKey, $test->getLog());
        $this->assertEquals($parameters, $test->getParameters());
        $this->assertEquals('log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals(VisibilityEnum::REVEALED, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
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
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

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

        $this->assertEquals($logKey, $test->getLog());
        $this->assertEquals($parameters, $test->getParameters());
        $this->assertEquals('log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals(VisibilityEnum::REVEALED, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
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
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;
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

        $this->assertEquals($logKey, $test->getLog());
        $this->assertEquals($parameters, $test->getParameters());
        $this->assertEquals('log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateActionSuccessLog()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

        $actionResult = new Success();
        $actionResult->setVisibility(VisibilityEnum::PUBLIC);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $test = $this->service->createLogFromActionResult(
            ActionEnum::STRENGTHEN_HULL,
            $actionResult,
            $player,
            null,
            new \DateTime()
        );

        $this->assertEquals(ActionLogEnum::STRENGTHEN_SUCCESS, $test->getLog());
        $this->assertEquals(['character' => 'andie'], $test->getParameters());
        $this->assertEquals('actions_log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals(VisibilityEnum::PUBLIC, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateActionFailLog()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

        $actionResult = new Fail();
        $actionResult->setVisibility(VisibilityEnum::PRIVATE);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $test = $this->service->createLogFromActionResult(
            ActionEnum::STRENGTHEN_HULL,
            $actionResult,
            $player,
            null,
            new \DateTime()
        );

        $this->assertEquals(ActionLogEnum::DEFAULT_FAIL, $test->getLog());
        $this->assertEquals(['character' => 'andie'], $test->getParameters());
        $this->assertEquals('actions_log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals(VisibilityEnum::PRIVATE, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateActionWithParameterLog()
    {
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $place = new Place();
        $place->setDaedalus($daedalus)->setName('test');

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

        $equipmentConfig = new EquipmentConfig();
        $gameEquipment = new GameEquipment(new Place());
        $gameEquipment->setName('equipment')->setEquipment($equipmentConfig);

        $actionResult = new Fail($gameEquipment);
        $actionResult->setVisibility(VisibilityEnum::PRIVATE);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $test = $this->service->createLogFromActionResult(
            ActionEnum::STRENGTHEN_HULL,
            $actionResult,
            $player,
            $gameEquipment,
            new \DateTime()
        );

        $this->assertEquals(ActionLogEnum::DEFAULT_FAIL, $test->getLog());
        $this->assertEquals(['character' => 'andie', 'target_equipment' => 'equipment'], $test->getParameters());
        $this->assertEquals('actions_log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals(VisibilityEnum::PRIVATE, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testGetLogs()
    {
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $gameConfig = new GameConfig();

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, new GameConfig(), new LocalizationConfig());
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $place = new Place();

        $player = new Player();
        $player->setPlace($place)->setDaedalus($daedalus);

        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $date = new \DateTime();

        $roomLog1 = new RoomLog();
        $roomLog1
            ->setLog('logKey1')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDate($date)
            ->setParameters([])
            ->setDay(1)
            ->setCycle(3)
            ->setType('log')
        ;

        $roomLog2 = new RoomLog();
        $roomLog2
            ->setLog('logKey2')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDate($date)
            ->setParameters(['player' => 'andie'])
            ->setDay(1)
            ->setCycle(4)
            ->setType('log')
        ;

        $this->repository
            ->shouldReceive('getPlayerRoomLog')
            ->with($playerInfo)
            ->andReturn([$roomLog1, $roomLog2])
            ->once()
        ;

        $logs = $this->service->getRoomLog($player);

        $expectedLogs = new RoomLogCollection([$roomLog1, $roomLog2]);

        $this->assertEquals($expectedLogs, $logs);
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
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);
        $player
            ->setPlayerInfo($playerInfo)
            ->setPlace($place)
        ;

        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $player2 = new Player();
        $playerInfo2 = new PlayerInfo($player2, new User(), $characterConfig2);
        $playerInfo2->setGameStatus(GameStatusEnum::CLOSED);
        $player2
            ->setPlayerInfo($playerInfo2)
            ->setPlace($place)
        ;

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

        $this->assertEquals($logKey, $test->getLog());
        $this->assertEquals($parameters, $test->getParameters());
        $this->assertEquals('log', $test->getType());
        $this->assertEquals($place->getName(), $test->getPlace());
        $this->assertEquals($playerInfo, $test->getPlayerInfo());
        $this->assertEquals(VisibilityEnum::SECRET, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }
}
