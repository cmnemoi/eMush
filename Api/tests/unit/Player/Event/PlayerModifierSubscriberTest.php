<?php

namespace Mush\Test\Player\Event;

use Mockery;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Listener\PlayerVariableSubscriber;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerModifierSubscriberTest extends TestCase
{
    /** @var PlayerVariableServiceInterface|Mockery\Mock */
    private PlayerVariableServiceInterface $playerVariableService;
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private PlayerVariableSubscriber $playerModifierSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->playerVariableService = \Mockery::mock(PlayerVariableServiceInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $this->playerModifierSubscriber = new PlayerVariableSubscriber(
            $this->playerVariableService,
            $this->eventDispatcher,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testOnMovementPointModifier()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            3,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleGameVariableChange')
            ->with(PlayerVariableEnum::MOVEMENT_POINT, 3, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnActionPointModifier()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::ACTION_POINT,
            1,
            'movement point conversion',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleGameVariableChange')
            ->with(PlayerVariableEnum::ACTION_POINT, 1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnMoralPointModifier()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $player->setMoralPoint(1);

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -1,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleGameVariableChange')
            ->with(PlayerVariableEnum::MORAL_POINT, -1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);

        // 0 moral point left
        $player->setMoralPoint(0);

        $this->playerVariableService
            ->shouldReceive('handleGameVariableChange')
            ->with(PlayerVariableEnum::MORAL_POINT, -1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnHealthPointModifier()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $player->setHealthPoint(1);
        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            1,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleGameVariableChange')
            ->with(PlayerVariableEnum::HEALTH_POINT, 1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);

        // 0 health point left
        $player->setHealthPoint(0);

        $this->playerVariableService
            ->shouldReceive('handleGameVariableChange')
            ->with(PlayerVariableEnum::HEALTH_POINT, 1, $player)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnSatietyPointModifier()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            1,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleGameVariableChange')
            ->with(PlayerVariableEnum::SATIETY, 1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
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
            ->setInitHealthPoint($health)
        ;

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
        ;

        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );

        $player->setPlayerInfo($playerInfo);

        return $player;
    }
}
