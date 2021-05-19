<?php

namespace Mush\Test\Player\Event;

use Mockery;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Event\PlayerModifierSubscriber;
use Mush\Player\Service\PlayerVariableServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerModifierSubscriberTest extends TestCase
{
    /** @var PlayerVariableServiceInterface | Mockery\Mock */
    private PlayerVariableServiceInterface $playerVariableService;
    /** @var EventDispatcherInterface | Mockery\Mock */
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

    public function testOnMovementPointConversion()
    {
        $player = new Player();

        $event = new PlayerModifierEvent($player, 1, new \DateTime());

        $player->setActionPoint(1);

        $this->playerVariableService->shouldReceive('handleActionPointModifier')->once();
        $this->playerVariableService->shouldReceive('handleMovementPointModifier')->once();

        $this->playerModifierSubscriber->onMovementPointConversion($event);

        $player->setActionPoint(0);

        $this->expectException(\Exception::class);

        $this->playerModifierSubscriber->onMovementPointConversion($event);
    }
}
