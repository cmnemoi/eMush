<?php

namespace Mush\Test\Player\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Player\Service\PlayerVariableService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class PlayerVariableServiceTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var ActionModifierServiceInterface | Mockery\Mock */
    private ActionModifierServiceInterface $actionModifierService;

    private PlayerVariableService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->actionModifierService = Mockery::mock(ActionModifierServiceInterface::class);

        $this->service = new PlayerVariableService(
            $this->statusService,
            $this->roomLogService,
            $this->actionModifierService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testSatietyModifier()
    {
        $player = new Player();
        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::SATIETY);
        $modifier->setDelta(-1);

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->statusService->shouldReceive('createCoreStatus')->once();

        $modifier->setDelta(4);

        $this->service->modifyPlayerVariable($player, $modifier);

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::FULL_STOMACH);

        $modifier->setDelta(-1);

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(3, $player->getSatiety());
        $this->assertCount(0, $player->getStatuses());

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(2, $player->getSatiety());
        $this->assertCount(0, $player->getStatuses());
    }

    public function testMushSatietyModifier()
    {
        $player = new Player();
        $mushStatus = new Status($player);
        $mushStatus->setName(PlayerStatusEnum::MUSH);

        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::SATIETY);
        $modifier->setDelta(-1);

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->statusService->shouldReceive('createChargeStatus')->once();

        $modifier->setDelta(1);

        $this->service->modifyPlayerVariable($player, $modifier);

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::FULL_STOMACH);

        $modifier->setDelta(-1);

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(0, $player->getSatiety());
        $this->assertCount(2, $player->getStatuses());
    }

    public function testMoraleModifier()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setMaxMoralPoint(16);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $room = new Place();
        $characterConfig = new CharacterConfig();
        $characterConfig->setName('Toto');

        $player = new Player();
        $player
            ->setMoralPoint(5)
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setCharacterConfig($characterConfig)
        ;

        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::MORAL_POINT);
        $modifier->setDelta(-2);

        //go below 4 moral
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();
        $this->statusService->shouldReceive('createCoreStatus')->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(3, $player->getMoralPoint());

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::DEMORALIZED);

        //go below 1 moral
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();
        $this->statusService->shouldReceive('createCoreStatus')->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(1, $player->getMoralPoint());
        $this->assertCount(0, $player->getStatuses());

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::SUICIDAL);

        //regain more moral than suicidal threshold
        $modifier->setDelta(2);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();
        $this->statusService->shouldReceive('createCoreStatus')->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(3, $player->getMoralPoint());
        $this->assertCount(0, $player->getStatuses());

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::DEMORALIZED);

        //gain more than morale threshold
        $modifier->setDelta(22);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(16, $player->getMoralPoint());
        $this->assertCount(0, $player->getStatuses());
    }

    public function testActionPointModifier()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setMaxActionPoint(16);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $room = new Place();
        $characterConfig = new CharacterConfig();
        $characterConfig->setName('Toto');
        $player = new Player();
        $player
            ->setActionPoint(5)
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setCharacterConfig($characterConfig)
        ;

        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::ACTION_POINT);
        $modifier->setDelta(-2);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_ACTION_POINT)
            ->andReturn(16)
            ->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(3, $player->getActionPoint());

        //less than 0
        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::ACTION_POINT);
        $modifier->setDelta(-6);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_ACTION_POINT)
            ->andReturn(16)
            ->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(0, $player->getActionPoint());

        //more than threshold
        $modifier->setDelta(35);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_ACTION_POINT)
            ->andReturn(16)
            ->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(16, $player->getActionPoint());
    }

    public function testHealthPointModifier()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setMaxHealthPoint(16);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $room = new Place();
        $characterConfig = new CharacterConfig();
        $characterConfig->setName('Toto');
        $player = new Player();
        $player
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setPlace($room)
            ->setCharacterConfig($characterConfig)
        ;

        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::HEALTH_POINT);
        $modifier->setDelta(-2);

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_HEALTH_POINT)
            ->andReturn(16)
            ->once();
        $this->roomLogService->shouldReceive('createLog')->once();

        $this->service->modifyPlayerVariable($player, $modifier);

        $this->assertEquals(3, $player->getHealthPoint());
    }
}
