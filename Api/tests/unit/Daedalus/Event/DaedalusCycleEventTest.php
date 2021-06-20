<?php

namespace Mush\Tests\unit\Daedalus\Event;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Listener\DaedalusCycleSubscriber;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusCycleEventTest extends TestCase
{
    /** @var DaedalusServiceInterface | Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;
    /** @var DaedalusIncidentServiceInterface | Mockery\Mock */
    private DaedalusIncidentServiceInterface $daedalusIncidentService;
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private DaedalusCycleSubscriber $daedalusCycleSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->daedalusService = Mockery::mock(DaedalusServiceInterface::class);
        $this->daedalusIncidentService = Mockery::mock(DaedalusIncidentServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->daedalusCycleSubscriber = new DaedalusCycleSubscriber(
            $this->daedalusService,
            $this->daedalusIncidentService,
            $this->eventDispatcher
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testOnDaedalusDestruction()
    {
        $gameConfig = new GameConfig();

        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $daedalus->setHull(0);

        $player = new Player();
        $player->setGameStatus(GameStatusEnum::CURRENT);
        $player->setDaedalus($daedalus);

        $mush = new Status($player);
        $mush->setName(PlayerStatusEnum::MUSH);

        $date = new \DateTime('tomorrow');

        $event = new DaedalusCycleEvent($daedalus, $date);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(fn (DaedalusEvent $endDaedalusEvent, string $eventName) => ($endDaedalusEvent->getTime() === $date && $eventName === DaedalusEvent::END_DAEDALUS))
            ->once();

        $this->daedalusCycleSubscriber->onNewCycle($event);
    }
}
