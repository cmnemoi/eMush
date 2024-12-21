<?php

declare(strict_types=1);

namespace Mush\Tests\unit\RoomLog\Service;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService as FakeGetRandomInteger;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Factory\PlayerFactory;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Repository\InMemoryRoomLogRepository;
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
    private FakeD100Roll $d100Roll;
    private FakeGetRandomInteger $getRandomInteger;
    private InMemoryRoomLogRepository $repository;
    private TranslationServiceInterface $translationService;

    private RoomLogService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->d100Roll = new FakeD100Roll();
        $this->getRandomInteger = new FakeGetRandomInteger(result: 0);
        $this->repository = new InMemoryRoomLogRepository();
        $this->translationService = $this->createStub(TranslationServiceInterface::class);

        $this->service = new RoomLogService(
            $this->d100Roll,
            $this->getRandomInteger,
            $this->repository,
            $this->translationService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        $this->repository->clear();
    }

    public function testPersist()
    {
        // given a room log
        $log = new RoomLog();

        // when I persist it
        $persistedLog = $this->service->persist($log);

        // then I should find it in repository
        self::assertSame($log, $this->repository->findById($persistedLog->getId()));
    }

    public function testCreateSimpleLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $visibility = VisibilityEnum::PUBLIC;
        $type = 'log';
        $player = null;
        $parameters = [];
        $dateTime = new \DateTime();

        $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        $log = $this->repository->getOneBy(['log' => $logKey, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $place->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame($logKey, $log->getLog());
        self::assertSame([], $log->getParameters());
        self::assertSame('log', $log->getType());
        self::assertSame($place->getName(), $log->getPlace());
        self::assertSame($player, $log->getPlayerInfo());
        self::assertSame($visibility, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testCreateLogWithParameters()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');
        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setCharacterName('gioele');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
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

        $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        $log = $this->repository->getOneBy(['log' => $logKey, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $place->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame($logKey, $log->getLog());
        self::assertSame($parameters, $log->getParameters());
        self::assertSame('log', $log->getType());
        self::assertSame($place->getName(), $log->getPlace());
        self::assertSame($playerInfo, $log->getPlayerInfo());
        self::assertSame($visibility, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testCreateSecretLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
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

        $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        $log = $this->repository->getOneBy(['log' => $logKey, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $place->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame($logKey, $log->getLog());
        self::assertSame($parameters, $log->getParameters());
        self::assertSame('log', $log->getType());
        self::assertSame($place->getName(), $log->getPlace());
        self::assertSame($playerInfo, $log->getPlayerInfo());
        self::assertSame($visibility, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testCreateSecretRevealedLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setCharacterName('chao');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
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

        $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        $log = $this->repository->getOneBy(['log' => $logKey, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $place->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame($logKey, $log->getLog());
        self::assertSame($parameters, $log->getParameters());
        self::assertSame('log', $log->getType());
        self::assertSame($place->getName(), $log->getPlace());
        self::assertSame($playerInfo, $log->getPlayerInfo());
        self::assertSame(VisibilityEnum::REVEALED, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testCreateCovertRevealedLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
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

        $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        $log = $this->repository->getOneBy(['log' => $logKey, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $place->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame($logKey, $log->getLog());
        self::assertSame($parameters, $log->getParameters());
        self::assertSame('log', $log->getType());
        self::assertSame($place->getName(), $log->getPlace());
        self::assertSame($playerInfo, $log->getPlayerInfo());
        self::assertSame(VisibilityEnum::REVEALED, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testCreateCovertItemCameraLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
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

        $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        $log = $this->repository->getOneBy(['log' => $logKey, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $place->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame($logKey, $log->getLog());
        self::assertSame($parameters, $log->getParameters());
        self::assertSame('log', $log->getType());
        self::assertSame($place->getName(), $log->getPlace());
        self::assertSame($playerInfo, $log->getPlayerInfo());
        self::assertSame($visibility, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testCreateActionSuccessLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);
        $actionResult = new Success();
        $actionResult->setVisibility(VisibilityEnum::PUBLIC);

        $actionEvent = new ActionEvent(
            actionConfig: ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::STRENGTHEN_HULL)),
            actionProvider: $this->createStub(ActionProviderInterface::class),
            player: $player,
            tags: []
        );
        $actionEvent->setActionResult($actionResult);

        $this->service->createLogFromActionEvent($actionEvent);

        $log = $this->repository->getOneBy(['log' => ActionLogEnum::STRENGTHEN_SUCCESS, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $player->getPlace()->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame(ActionLogEnum::STRENGTHEN_SUCCESS, $log->getLog());
        self::assertSame(['character' => 'andie'], $log->getParameters());
        self::assertSame('actions_log', $log->getType());
        self::assertSame($player->getPlace()->getName(), $log->getPlace());
        self::assertSame($player->getPlayerInfo(), $log->getPlayerInfo());
        self::assertSame(VisibilityEnum::PUBLIC, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testCreateActionFailLog()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        $actionResult = new Fail();
        $actionResult->setVisibility(VisibilityEnum::PRIVATE);

        $actionEvent = new ActionEvent(
            actionConfig: ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::STRENGTHEN_HULL)),
            actionProvider: $this->createStub(ActionProviderInterface::class),
            player: $player,
            tags: []
        );
        $actionEvent->setActionResult($actionResult);

        $this->service->createLogFromActionEvent($actionEvent);

        $log = $this->repository->getOneBy(['log' => ActionLogEnum::DEFAULT_FAIL, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $player->getPlace()->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame(ActionLogEnum::DEFAULT_FAIL, $log->getLog());
        self::assertSame(['character' => 'andie'], $log->getParameters());
        self::assertSame('actions_log', $log->getType());
        self::assertSame($player->getPlace()->getName(), $log->getPlace());
        self::assertSame($player->getPlayerInfo(), $log->getPlayerInfo());
        self::assertSame(VisibilityEnum::PRIVATE, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
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

        $actionEvent = new ActionEvent(
            actionConfig: ActionConfig::fromConfigData(ActionData::getByName(ActionEnum::STRENGTHEN_HULL)),
            actionProvider: $this->createStub(ActionProviderInterface::class),
            player: $player,
            tags: [],
            actionTarget: $gameEquipment
        );
        $actionEvent->setActionResult($actionResult);

        $this->service->createLogFromActionEvent($actionEvent);

        $log = $this->repository->getOneBy(['log' => ActionLogEnum::DEFAULT_FAIL, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $player->getPlace()->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame(ActionLogEnum::DEFAULT_FAIL, $log->getLog());
        self::assertSame(['character' => 'andie', 'target_equipment' => 'equipment'], $log->getParameters());
        self::assertSame('actions_log', $log->getType());
        self::assertSame($player->getPlace()->getName(), $log->getPlace());
        self::assertSame($player->getPlayerInfo(), $log->getPlayerInfo());
        self::assertSame(VisibilityEnum::PRIVATE, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testGetLogs()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        $date = new \DateTime();

        $roomLog1 = new RoomLog();
        $roomLog1
            ->setLog('logKey1')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCreatedAt($date)
            ->setPlace($player->getPlace()->getName())
            ->setDaedalusInfo($daedalus->getDaedalusInfo())
            ->setParameters([])
            ->setDay(1)
            ->setCycle(3)
            ->setType('log');

        $roomLog2 = new RoomLog();
        $roomLog2
            ->setLog('logKey2')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCreatedAt($date)
            ->setPlace($player->getPlace()->getName())
            ->setDaedalusInfo($daedalus->getDaedalusInfo())
            ->setParameters(['player' => 'andie'])
            ->setDay(1)
            ->setCycle(4)
            ->setType('log');

        $this->repository->save($roomLog1);
        $this->repository->save($roomLog2);

        $logs = $this->service->getRoomLog($player);

        self::assertContains('logKey1', $logs->map(static fn (RoomLog $roomLog) => $roomLog->getLog())->toArray());
        self::assertContains('logKey2', $logs->map(static fn (RoomLog $roomLog) => $roomLog->getLog())->toArray());
    }

    public function testCreateSecretLogDeadPlayerInRoom()
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->setCycle(4);
        $daedalus->setDay(2);

        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName('andie');

        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setCharacterName('chao');

        $logKey = ActionLogEnum::OPEN_SUCCESS;
        $place = $daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
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

        $this->service->createLog(
            ActionLogEnum::OPEN_SUCCESS,
            $place,
            $visibility,
            $type,
            $player,
            $parameters,
            $dateTime
        );

        $log = $this->repository->getOneBy(['log' => $logKey, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $place->getName(), 'day' => 2, 'cycle' => 4]);

        self::assertSame($logKey, $log->getLog());
        self::assertSame($parameters, $log->getParameters());
        self::assertSame('log', $log->getType());
        self::assertSame($place->getName(), $log->getPlace());
        self::assertSame($playerInfo, $log->getPlayerInfo());
        self::assertSame(VisibilityEnum::SECRET, $log->getVisibility());
        self::assertSame(4, $log->getCycle());
        self::assertSame(2, $log->getDay());
    }

    public function testMarkAllRoomLogsAsReadForPlayer()
    {
        // given a player
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        // given some unread room logs
        $roomLogs = $this->roomLogFactory($player->getPlayerInfo(), number: 5);

        // when I call markAllRoomLogsAsReadForPlayer
        $this->service->markAllRoomLogsAsReadForPlayer($player);

        // then all player room logs should be marked as read
        $roomLogs->map(function (RoomLog $roomLog) use ($player) {
            $this->assertTrue($roomLog->isReadBy($player));
        });
    }

    public function testMarkRoomLogAsReadForPlayer(): void
    {
        // given a player
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, $daedalus);

        // given 1 unread room log
        $roomLogs = $this->roomLogFactory($player->getPlayerInfo(), number: 1);

        // when I call markRoomLogAsReadForPlayer
        $this->service->markRoomLogAsReadForPlayer($roomLogs->get(0), $player);

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

        $this->d100Roll->makeSuccessful();

        $this->service->createLog(
            ActionLogEnum::MAKE_SICK,
            $player->getPlace(),
            VisibilityEnum::COVERT,
            'log',
            $player,
            [],
            new \DateTime()
        );

        $roomLog = $this->repository->getOneBy(['log' => ActionLogEnum::MAKE_SICK, 'daedalusInfo' => $daedalus->getDaedalusInfo(), 'place' => $player->getPlace()->getName(), 'day' => 1, 'cycle' => 1]);

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
                ->setDaedalusInfo($playerInfo->getPlayer()->getDaedalusInfo())
                ->setPlace($playerInfo->getPlayer()->getPlace()->getName())
                ->setParameters([])
                ->setDay(1)
                ->setCycle(1)
                ->setType('log');

            $roomLogs->add($roomLog);
            $this->repository->save($roomLog);
        }

        return $roomLogs;
    }
}
