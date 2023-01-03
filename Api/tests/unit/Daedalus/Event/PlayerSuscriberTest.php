<?php

namespace Mush\Tests\unit\Daedalus\Event;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Listener\PlayerSubscriber;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerEvent;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerSuscriberTest extends TestCase
{
    /** @var DaedalusServiceInterface|Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private PlayerSubscriber $playerSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->daedalusService = \Mockery::mock(DaedalusServiceInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $this->playerSubscriber = new PlayerSubscriber(
            $this->daedalusService,
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
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setInitHull(0);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $daedalusInfo->setGameStatus(GameStatusEnum::CURRENT);
        $daedalus->setDaedalusVariables($daedalusConfig);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $playerInfo->setGameStatus(GameStatusEnum::FINISHED);
        $player
            ->setDaedalus($daedalus)
            ->setPlayerInfo($playerInfo)
        ;

        $date = new \DateTime('tomorrow');

        $event = new PlayerEvent(
            $player,
            DaedalusEvent::FINISH_DAEDALUS,
            $date
        );

        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(fn (DaedalusEvent $endDaedalusEvent, string $eventName) => ($endDaedalusEvent->getTime() === $date && $eventName === DaedalusEvent::FINISH_DAEDALUS))
            ->once();

        $this->playerSubscriber->onDeathPlayer($event);
    }
}
