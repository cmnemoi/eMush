<?php

namespace Mush\Test\Player\Event;

use Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Listener\PlayerModifierSubscriber;
use Mush\Player\Service\PlayerVariableServiceInterface;
use PHPUnit\Framework\TestCase;

class PlayerModifierSubscriberTest extends TestCase
{
    /** @var PlayerVariableServiceInterface|Mockery\Mock */
    private PlayerVariableServiceInterface $playerVariableService;
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private PlayerModifierSubscriber $playerModifierSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->playerVariableService = Mockery::mock(PlayerVariableServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(eventDispatcherInterface::class);

        $this->playerModifierSubscriber = new PlayerModifierSubscriber(
            $this->playerVariableService,
            $this->eventDispatcher,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testOnMovementPointModifier()
    {
        $player = new Player();

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MOVEMENT_POINT,
            3,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleMovementPointModifier')
            ->with(3, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnActionPointModifier()
    {
        $player = new Player();

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::ACTION_POINT,
            1,
            'movement point conversion',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleActionPointModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnMoralPointModifier()
    {
        $player = new Player();

        $player->setMoralPoint(1);

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::MORAL_POINT,
            -1,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleMoralPointModifier')
            ->with(-1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);

        // 0 moral point left
        $player->setMoralPoint(0);

        $this->playerVariableService
            ->shouldReceive('handleMoralPointModifier')
            ->with(-1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnHealthPointModifier()
    {
        $player = new Player();

        $player->setHealthPoint(1);
        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::HEALTH_POINT,
            1,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleHealthPointModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);

        // 0 health point left
        $player->setHealthPoint(0);

        $this->playerVariableService
            ->shouldReceive('handleHealthPointModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerModifierSubscriber->onChangeVariable($event);
    }

    public function testOnSatietyPointModifier()
    {
        $player = new Player();

        $event = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SATIETY,
            1,
            'reason',
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleSatietyModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onChangeVariable($event);
    }
}
