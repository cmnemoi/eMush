<?php

namespace Mush\Test\Player\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Mush\RoomLog\Service\RoomLogService;
use PHPUnit\Framework\TestCase;

class RoomLogServiceTest extends TestCase
{
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var RoomLogRepository|Mockery\Mock */
    private RoomLogRepository $repository;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    private RoomLogService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->repository = Mockery::mock(RoomLogRepository::class);
        $this->translationService = Mockery::mock(TranslationServiceInterface::class);

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
        Mockery::close();
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
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus);
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
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateLogWithParameters()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');
        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setName('gioele');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus);
        $visibility = VisibilityEnum::PUBLIC;
        $type = 'log';
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);

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
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateCovertLog()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus);
        $visibility = VisibilityEnum::COVERT;
        $type = 'log';
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);
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
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateCovertRevealedLog()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus);
        $visibility = VisibilityEnum::COVERT;
        $type = 'log';
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);
        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $player2 = new Player();
        $player2->setPlace($place);

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
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals(VisibilityEnum::REVEALED, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateSecretRevealedLog()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus);
        $visibility = VisibilityEnum::SECRET;
        $type = 'log';
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);
        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $cameraEquipment = new GameEquipment();
        $cameraEquipment->setName(EquipmentEnum::CAMERA_EQUIPMENT)->setHolder($place);

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
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals(VisibilityEnum::REVEALED, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateSecretItemCameraLog()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = new Place();
        $place->setDaedalus($daedalus);
        $visibility = VisibilityEnum::SECRET;
        $type = 'log';
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);
        $parameters = ['character' => 'andie'];
        $dateTime = new \DateTime();

        $cameraEquipment = new GameItem();
        $cameraEquipment->setName(ItemEnum::CAMERA_ITEM)->setHolder($place);

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
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals($visibility, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateActionSuccessLog()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $place = new Place();
        $place->setDaedalus($daedalus);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);

        $actionResult = new Success();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $test = $this->service->createLogFromActionResult(ActionEnum::STRENGTHEN_HULL, $actionResult, $player, null);

        $this->assertEquals(ActionLogEnum::STRENGTHEN_SUCCESS, $test->getLog());
        $this->assertEquals(['character' => 'andie'], $test->getParameters());
        $this->assertEquals('actions_log', $test->getType());
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals(VisibilityEnum::PUBLIC, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateActionFailLog()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $place = new Place();
        $place->setDaedalus($daedalus);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);

        $actionResult = new Fail();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $test = $this->service->createLogFromActionResult(ActionEnum::STRENGTHEN_HULL, $actionResult, $player, null);

        $this->assertEquals(ActionLogEnum::DEFAULT_FAIL, $test->getLog());
        $this->assertEquals(['character' => 'andie'], $test->getParameters());
        $this->assertEquals('actions_log', $test->getType());
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals(VisibilityEnum::PRIVATE, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testCreateActionWithParameterLog()
    {
        $daedalus = new Daedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $place = new Place();
        $place->setDaedalus($daedalus);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName('andie');
        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setPlace($place);

        $gameEquipment = new GameEquipment();
        $gameEquipment->setName('equipment');

        $actionResult = new Fail($gameEquipment);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $test = $this->service->createLogFromActionResult(ActionEnum::STRENGTHEN_HULL, $actionResult, $player, $gameEquipment);

        $this->assertEquals(ActionLogEnum::DEFAULT_FAIL, $test->getLog());
        $this->assertEquals(['character' => 'andie', 'target_equipment' => 'equipment'], $test->getParameters());
        $this->assertEquals('actions_log', $test->getType());
        $this->assertEquals($place, $test->getPlace());
        $this->assertEquals($player, $test->getPlayer());
        $this->assertEquals(VisibilityEnum::PRIVATE, $test->getVisibility());
        $this->assertEquals(4, $test->getCycle());
        $this->assertEquals(2, $test->getDay());
    }

    public function testGetLogs()
    {
        $place = new Place();

        $player = new Player();
        $player->setPlace($place);

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
            ->with($player)
            ->andReturn([$roomLog1, $roomLog2])
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('logKey1', [], 'log')
            ->andReturn('translated log 1')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('logKey2', ['player' => 'andie'], 'log')
            ->andReturn('translated log 2')
            ->once()
        ;

        $logs = $this->service->getRoomLog($player);

        $expectedLogs = [1 => [
            3 => [['log' => 'translated log 1', 'visibility' => VisibilityEnum::PUBLIC, 'date' => $date]],
            4 => [['log' => 'translated log 2', 'visibility' => VisibilityEnum::PUBLIC, 'date' => $date]],
            ]];

        $this->assertEquals($expectedLogs, $logs);
    }
}
