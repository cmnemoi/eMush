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
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerEvent;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerSuscriberTest extends TestCase
{
    /** @var DaedalusServiceInterface|Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    private PlayerSubscriber $playerSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->daedalusService = \Mockery::mock(DaedalusServiceInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);

        $this->playerSubscriber = new PlayerSubscriber(
            $this->daedalusService,
            $this->eventService
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
            ->setPlayerInfo($playerInfo);

        $date = new \DateTime('tomorrow');

        $event = new PlayerEvent(
            $player,
            [DaedalusEvent::FINISH_DAEDALUS],
            $date
        );

        $this->eventService->shouldReceive('callEvent')
            ->withArgs(static fn (DaedalusEvent $endDaedalusEvent, string $eventName) => ($endDaedalusEvent->getTime() === $date && $eventName === DaedalusEvent::FINISH_DAEDALUS))
            ->once();

        $this->playerSubscriber->onDeathPlayer($event);
    }
}
