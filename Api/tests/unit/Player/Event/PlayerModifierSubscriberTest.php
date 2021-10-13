<?php

namespace Mush\Test\Player\Event;

use Mockery;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Listener\PlayerModifierSubscriber;
use Mush\Player\Service\PlayerVariableServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

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

        $event = new PlayerModifierEvent(
            $player,
            3,
            PlayerModifierEvent::MOVEMENT_POINT_MODIFIER,
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleMovementPointModifier')
            ->with(3, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onMovementPointModifier($event);
    }

    public function testOnActionPointModifier()
    {
        $player = new Player();

        $event = new PlayerModifierEvent($player, 1, 'movement point conversion', new \DateTime());

        $this->playerVariableService
            ->shouldReceive('handleActionPointModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onActionPointModifier($event);
    }

    public function testOnMoralPointModifier()
    {
        $player = new Player();

        $player->setMoralPoint(1);

        $event = new PlayerModifierEvent(
            $player,
            -1,
            PlayerModifierEvent::MOVEMENT_POINT_MODIFIER,
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleMoralPointModifier')
            ->with(-1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onMoralPointModifier($event);

        // 0 moral point left
        $player->setMoralPoint(0);

        $this->playerVariableService
            ->shouldReceive('handleMoralPointModifier')
            ->with(-1, $player)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerModifierSubscriber->onMoralPointModifier($event);
    }

    public function testOnHealthPointModifier()
    {
        $player = new Player();

        $player->setHealthPoint(1);
        $event = new PlayerModifierEvent(
            $player,
            1,
            PlayerModifierEvent::MOVEMENT_POINT_MODIFIER,
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleHealthPointModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onHealthPointModifier($event);

        // 0 health point left
        $player->setHealthPoint(0);

        $this->playerVariableService
            ->shouldReceive('handleHealthPointModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerModifierSubscriber->onHealthPointModifier($event);
    }

    public function testOnSatietyPointModifier()
    {
        $player = new Player();

        $event = new PlayerModifierEvent(
            $player,
            1,
            PlayerModifierEvent::MOVEMENT_POINT_MODIFIER,
            new \DateTime()
        );

        $this->playerVariableService
            ->shouldReceive('handleSatietyModifier')
            ->with(1, $player)
            ->once()
        ;

        $this->playerModifierSubscriber->onSatietyPointModifier($event);
    }
}
