<?php

namespace Mush\Tests\unit\Player\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableService;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerVariableServiceTest extends TestCase
{
    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    private PlayerVariableService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->service = new PlayerVariableService(
            $this->playerService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testSatietyModifier()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::SATIETY, -1, $player);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::SATIETY, 4, $player);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::SATIETY, -1, $player);

        self::assertSame(3, $player->getSatiety());

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::SATIETY, -1, $player);

        self::assertSame(2, $player->getSatiety());
    }

    public function testMushSatietyModifier()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::SATIETY, -1, $player);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::SATIETY, 1, $player);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::SATIETY, -1, $player);

        self::assertSame(0, $player->getSatiety());
    }

    public function testMoraleModifier()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setMaxMoralPoint(16);
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player
            ->setMoralPoint(5)
            ->setDaedalus($daedalus)
            ->setPlace($room);

        // go below 4 moral
        $this->playerService->shouldReceive('persist')->once();

        $this->service->handleGameVariableChange(PlayerVariableEnum::MORAL_POINT, -2, $player);

        self::assertSame(3, $player->getMoralPoint());

        // go below 1 moral
        $this->playerService->shouldReceive('persist')->once();

        $this->service->handleGameVariableChange(PlayerVariableEnum::MORAL_POINT, -2, $player);

        self::assertSame(1, $player->getMoralPoint());

        // regain more moral than suicidal threshold
        $this->playerService->shouldReceive('persist')->once();

        $this->service->handleGameVariableChange(PlayerVariableEnum::MORAL_POINT, 2, $player);

        self::assertSame(3, $player->getMoralPoint());

        // $status = new Status($player, PlayerStatusEnum::DEMORALIZED);

        // gain more than morale threshold
        $this->playerService->shouldReceive('persist')->once();

        $this->service->handleGameVariableChange(PlayerVariableEnum::MORAL_POINT, 22, $player);

        self::assertSame(16, $player->getMoralPoint());
    }

    public function testActionPointModifier()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setMaxActionPoint(16);

        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player
            ->setActionPoint(5)
            ->setDaedalus($daedalus)
            ->setPlace($room);

        $this->playerService->shouldReceive('persist')->once();

        $this->service->handleGameVariableChange(PlayerVariableEnum::ACTION_POINT, -2, $player);

        self::assertSame(3, $player->getActionPoint());

        // less than 0
        $this->playerService->shouldReceive('persist')->once();

        $this->service->handleGameVariableChange(PlayerVariableEnum::ACTION_POINT, -6, $player);

        self::assertSame(0, $player->getActionPoint());

        // more than threshold
        $this->playerService->shouldReceive('persist')->once();

        $this->service->handleGameVariableChange(PlayerVariableEnum::ACTION_POINT, 35, $player);

        self::assertSame(16, $player->getActionPoint());
    }

    public function testHealthPointModifier()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig->setMaxHealthPoint(16);

        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player
            ->setHealthPoint(5)
            ->setDaedalus($daedalus)
            ->setPlace($room);
        $playerInfo = new PlayerInfo($player, new User(), $characterConfig);

        $this->playerService->shouldReceive('persist')->once();
        $this->service->handleGameVariableChange(PlayerVariableEnum::HEALTH_POINT, -2, $player);

        self::assertSame(3, $player->getHealthPoint());
    }

    protected function createPlayer(int $health, int $moral, int $movement, int $action, int $satiety): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setMaxHealthPoint(16)
            ->setMaxMoralPoint(16)
            ->setMaxActionPoint(16)
            ->setMaxMovementPoint(16)
            ->setInitActionPoint($action)
            ->setInitMovementPoint($movement)
            ->setInitMoralPoint($moral)
            ->setInitSatiety($satiety)
            ->setInitHealthPoint($health);

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig);

        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );

        $player->setPlayerInfo($playerInfo);

        return $player;
    }
}
