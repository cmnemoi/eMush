<?php

namespace Mush\Test\Player\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableService;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;

class PlayerVariableServiceTest extends TestCase
{
    /** @var ModifierServiceInterface|Mockery\Mock */
    private ModifierServiceInterface $modifierService;
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;

    private PlayerVariableService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierService = Mockery::mock(ModifierServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->service = new PlayerVariableService(
            $this->modifierService,
            $this->playerService,
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

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleSatietyModifier(-1, $player);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleSatietyModifier(4, $player);

        $status = new Status($player, PlayerStatusEnum::FULL_STOMACH);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleSatietyModifier(-1, $player);

        $this->assertEquals(3, $player->getSatiety());

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleSatietyModifier(-1, $player);

        $this->assertEquals(2, $player->getSatiety());
    }

    public function testMushSatietyModifier()
    {
        $player = new Player();
        $mushStatus = new Status($player, PlayerStatusEnum::MUSH);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleSatietyModifier(-1, $player);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleSatietyModifier(1, $player);

        $status = new Status($player, PlayerStatusEnum::FULL_STOMACH);

        $this->playerService->shouldReceive('persist')->once();
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
        $this->playerService->shouldReceive('persist')->once();
        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::MORAL_POINT, 16)
            ->andReturn(16)
            ->once();

        $this->service->handleMoralPointModifier(-2, $player);

        $this->assertEquals(3, $player->getMoralPoint());

        $status = new Status($player, PlayerStatusEnum::DEMORALIZED);

        //go below 1 moral
        $this->playerService->shouldReceive('persist')->once();
        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::MORAL_POINT, 16)
            ->andReturn(16)
            ->once();

        $this->service->handleMoralPointModifier(-2, $player);

        $this->assertEquals(1, $player->getMoralPoint());

        $status = new Status($player, PlayerStatusEnum::SUICIDAL);

        //regain more moral than suicidal threshold
        $this->playerService->shouldReceive('persist')->once();
        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::MORAL_POINT, 16)
            ->andReturn(16)
            ->once();

        $this->service->handleMoralPointModifier(2, $player);

        $this->assertEquals(3, $player->getMoralPoint());

        $status = new Status($player, PlayerStatusEnum::DEMORALIZED);

        //gain more than morale threshold
        $this->playerService->shouldReceive('persist')->once();
        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::MORAL_POINT, 16)
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

        $this->playerService->shouldReceive('persist')->once();
        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::ACTION_POINT, 16)
            ->andReturn(16)
            ->once();

        $this->service->handleActionPointModifier(-2, $player);

        $this->assertEquals(3, $player->getActionPoint());

        //less than 0
        $this->playerService->shouldReceive('persist')->once();
        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::ACTION_POINT, 16)
            ->andReturn(16)
            ->once();

        $this->service->handleActionPointModifier(-6, $player);

        $this->assertEquals(0, $player->getActionPoint());

        //more than threshold
        $this->playerService->shouldReceive('persist')->once();
        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::ACTION_POINT, 16)
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

        $this->modifierService->shouldReceive('getEventModifiedValue')
            ->with($player, [ModifierScopeEnum::MAX_POINT], ModifierTargetEnum::HEALTH_POINT, 16)
            ->andReturn(16)
            ->once();
        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleHealthPointModifier(-2, $player);

        $this->assertEquals(3, $player->getHealthPoint());
    }
}
