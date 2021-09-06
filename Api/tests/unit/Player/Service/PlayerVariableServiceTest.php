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
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;

class PlayerVariableServiceTest extends TestCase
{
    /** @var ActionModifierServiceInterface|Mockery\Mock */
    private ActionModifierServiceInterface $actionModifierService;

    private PlayerVariableService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->actionModifierService = Mockery::mock(ActionModifierServiceInterface::class);

        $this->service = new PlayerVariableService(
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

        $this->service->handleSatietyModifier(-1, $player);

        $this->service->handleSatietyModifier(4, $player);

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::FULL_STOMACH);

        $this->service->handleSatietyModifier(-1, $player);

        $this->assertEquals(3, $player->getSatiety());

        $this->service->handleSatietyModifier(-1, $player);

        $this->assertEquals(2, $player->getSatiety());
    }

    public function testMushSatietyModifier()
    {
        $player = new Player();
        $mushStatus = new Status($player);
        $mushStatus->setName(PlayerStatusEnum::MUSH);

        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::SATIETY);
        $modifier->setDelta(-1);

        $this->service->handleSatietyModifier(-1, $player);

        $modifier->setDelta(1);

        $this->service->handleSatietyModifier(1, $player);

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::FULL_STOMACH);

        $this->service->handleSatietyModifier(-1, $player);

        $this->assertEquals(0, $player->getSatiety());
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

        //go below 4 moral
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();

        $this->service->handleMoralPointModifier(-2, $player);

        $this->assertEquals(3, $player->getMoralPoint());

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::DEMORALIZED);

        //go below 1 moral
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();

        $this->service->handleMoralPointModifier(-2, $player);

        $this->assertEquals(1, $player->getMoralPoint());

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::SUICIDAL);

        //regain more moral than suicidal threshold
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();

        $this->service->handleMoralPointModifier(2, $player);

        $this->assertEquals(3, $player->getMoralPoint());

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::DEMORALIZED);

        //gain more than morale threshold
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_MORAL_POINT)
            ->andReturn(16)
            ->once();

        $this->service->handleMoralPointModifier(22, $player);

        $this->assertEquals(16, $player->getMoralPoint());
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

        $this->service->handleActionPointModifier(-2, $player);

        $this->assertEquals(3, $player->getActionPoint());

        //less than 0
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_ACTION_POINT)
            ->andReturn(16)
            ->once();

        $this->service->handleActionPointModifier(-6, $player);

        $this->assertEquals(0, $player->getActionPoint());

        //more than threshold
        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_ACTION_POINT)
            ->andReturn(16)
            ->once();

        $this->service->handleActionPointModifier(35, $player);

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

        $this->actionModifierService->shouldReceive('getModifiedValue')
            ->with(16, $player, [ModifierScopeEnum::PERMANENT], ModifierTargetEnum::MAX_HEALTH_POINT)
            ->andReturn(16)
            ->once();

        $this->service->handleHealthPointModifier(-2, $player);

        $this->assertEquals(3, $player->getHealthPoint());
    }
}
