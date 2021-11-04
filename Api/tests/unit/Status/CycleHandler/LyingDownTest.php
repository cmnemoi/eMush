<?php

namespace Mush\Test\Status\CycleHandler;

use Mockery;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\CycleHandler\LyingDown;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LyingDownTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private LyingDown $cycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->cycleHandler = new LyingDown($this->eventDispatcher);
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
        $room = new Place();

        $player = new Player();
        $player
            ->setPlace($room)
        ;

        $statusConfig = new StatusConfig();
        $statusConfig->setName(PlayerStatusEnum::LYING_DOWN);
        $status = new Status($player, $statusConfig);

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->cycleHandler->handleNewCycle($status, $player, new \DateTime());

        $this->assertTrue(true);
    }
}
