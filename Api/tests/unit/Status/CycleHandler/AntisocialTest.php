<?php

namespace Mush\Test\Status\CycleHandler;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\CycleHandler\Antisocial;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AntisocialTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private Antisocial $cycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->cycleHandler = new Antisocial($this->eventDispatcher);
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

        $status = new Status($player);
        $status
            ->setName(PlayerStatusEnum::ANTISOCIAL)
        ;

        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->cycleHandler->handleNewCycle($status, new Daedalus(), $player, new \DateTime());

        $otherPlayer = new Player();
        $otherPlayer->setRoom(new Room());

        $this->eventDispatcher->shouldReceive('dispatch')->never();
        $this->cycleHandler->handleNewCycle($status, new Daedalus(), $player, new \DateTime());

        $otherPlayer->setRoom($room);

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->cycleHandler->handleNewCycle($status, new Daedalus(), $player, new \DateTime());

        $this->assertTrue(true);
    }
}
