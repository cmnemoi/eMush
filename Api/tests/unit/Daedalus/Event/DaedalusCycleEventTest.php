<?php

namespace Mush\Tests\unit\Daedalus\Event;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Listener\DaedalusCycleSubscriber;
use Mush\Daedalus\Service\DaedalusIncidentServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusCycleEventTest extends TestCase
{
    /** @var DaedalusServiceInterface|Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;
    /** @var DaedalusIncidentServiceInterface|Mockery\Mock */
    private DaedalusIncidentServiceInterface $daedalusIncidentService;
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private DaedalusCycleSubscriber $daedalusCycleSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->daedalusService = \Mockery::mock(DaedalusServiceInterface::class);
        $this->daedalusIncidentService = \Mockery::mock(DaedalusIncidentServiceInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);

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
        \Mockery::close();
    }

    public function testOnDaedalusDestruction()
    {
        $gameConfig = new GameConfig();

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $daedalus->setHull(0);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $player->setDaedalus($daedalus)->setPlayerInfo($playerInfo);

        $mushConfig = new StatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $mush = new Status($player, $mushConfig);

        $date = new \DateTime('tomorrow');

        $event = new DaedalusCycleEvent($daedalus, DaedalusEvent::FINISH_DAEDALUS, $date);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(fn (DaedalusEvent $endDaedalusEvent, string $eventName) => ($endDaedalusEvent->getTime() === $date && $eventName === DaedalusEvent::FINISH_DAEDALUS))
            ->once();

        $this->daedalusCycleSubscriber->onNewCycle($event);
    }
}
