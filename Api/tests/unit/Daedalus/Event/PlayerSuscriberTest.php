<?php

namespace Mush\Tests\unit\Daedalus\Event;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\PlayerSubscriber;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerSuscriberTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private PlayerSubscriber $playerSubscriber;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->playerSubscriber = new PlayerSubscriber(
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
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);
        $daedalus->setHull(0);

        $player = new Player();
        $player->setGameStatus(GameStatusEnum::FINISHED);
        $player->setDaedalus($daedalus);

        $date = new \DateTime('tomorrow');

        $event = new PlayerEvent($player, $date);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(fn (DaedalusEvent $endDaedalusEvent, string $eventName) => ($endDaedalusEvent->getTime() === $date && $eventName === DaedalusEvent::END_DAEDALUS))
            ->once();

        $this->playerSubscriber->onDeathPlayer($event);
    }
}
