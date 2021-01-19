<?php

namespace Mush\Test\Status\CycleHandler;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\CycleHandler\Lost;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LostTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private Lost $cycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->cycleHandler = new Lost($this->eventDispatcher);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNewCycle()
    {
        $room = new Room();

        $player = new Player();
        $player
            ->setRoom($room)
        ;

        $status = new Status();
        $status
            ->setName(PlayerStatusEnum::LOST)
        ;

        $player
            ->addStatus($status)
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
        ;
        $this->cycleHandler->handleNewCycle($status, new Daedalus(), $player, new \DateTime());

        $this->assertTrue(true);
    }
}
