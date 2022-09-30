<?php

namespace Mush\Tests\unit\Daedalus\Event;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Listener\PlayerSubscriber;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use PHPUnit\Framework\TestCase;
use Mush\Game\Service\EventServiceInterface;

class PlayerSuscriberTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    private PlayerSubscriber $playerSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = Mockery::mock(EventServiceInterface::class);

        $this->playerSubscriber = new PlayerSubscriber(
            $this->eventService
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
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);
        $daedalus->setHull(0);

        $player = new Player();
        $player->setGameStatus(GameStatusEnum::FINISHED);
        $player->setDaedalus($daedalus);

        $date = new \DateTime('tomorrow');

        $event = new PlayerEvent(
            $player,
            DaedalusEvent::END_DAEDALUS,
            $date
        );

        $this->eventService->shouldReceive('dispatch')
            ->withArgs(fn (DaedalusEvent $endDaedalusEvent, string $eventName) => ($endDaedalusEvent->getTime() === $date && $eventName === DaedalusEvent::END_DAEDALUS))
            ->once();

        $this->playerSubscriber->onDeathPlayer($event);
    }
}
