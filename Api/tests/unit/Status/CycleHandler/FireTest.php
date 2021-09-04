<?php

namespace Mush\Test\Status\CycleHandler;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\CycleHandler\Fire;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FireTest extends TestCase
{
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var DaedalusServiceInterface | Mockery\Mock */
    private DaedalusServiceInterface $daedalusService;
    private Fire $cycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->daedalusService = Mockery::mock(DaedalusServiceInterface::class);

        $this->cycleHandler = new Fire(
            $this->randomService,
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->daedalusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNewCycleFireDamage()
    {
        $room = new Place();

        $difficultyConfig = new DifficultyConfig();
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig->setMaxHull(100);

        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $gameConfig
            ->setDifficultyConfig($difficultyConfig)
            ->setDaedalusConfig($daedalusConfig)
        ;
        $daedalus->setGameConfig($gameConfig);
        $room->setDaedalus($daedalus);

        $daedalusHull = 100;
        $daedalus->setHull($daedalusHull);

        $status = new ChargeStatus($room);
        $status
            ->setName(StatusEnum::FIRE)
            ->setCharge(1)
        ;

        $player = new Player();
        $player->setGameStatus(GameStatusEnum::CURRENT);
        $room->addPlayer($player);

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaArray')->andReturn(2)->twice();
        $this->daedalusService->shouldReceive('persist')->once();

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (PlayerModifierEvent $playerEvent, string $eventName) => (
                intval($playerEvent->getQuantity()) === -2 && $eventName === PlayerModifierEvent::HEALTH_POINT_MODIFIER
            ))
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (DaedalusModifierEvent $daedalusEvent, string $eventName) => ($eventName === DaedalusModifierEvent::CHANGE_HULL))
            ->once()
        ;

        $this->cycleHandler->handleNewCycle($status, $room, new \DateTime());

        $this->assertEquals($daedalusHull, $daedalus->getHull());
    }
}
