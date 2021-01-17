<?php

namespace Mush\Test\Status\CycleHandler;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Room\Entity\Room;
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
        $room = new Room();

        $difficultyConfig = new DifficultyConfig();
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $gameConfig->setDifficultyConfig($difficultyConfig);
        $daedalus->setGameConfig($gameConfig);
        $room->setDaedalus($daedalus);

        $daedalusHull = 100;
        $daedalus->setHull($daedalusHull);

        $status = new ChargeStatus();
        $status
            ->setName(StatusEnum::FIRE)
            ->setCharge(1)
            ->setRoom($room)
        ;

        $player = new Player();
        $room->addPlayer($player);

        $this->randomService->shouldReceive('isSuccessfull')->andReturn(true)->once();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaArray')->andReturn(2)->twice();
        $this->daedalusService->shouldReceive('persist')->once();

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (PlayerEvent $playerEvent) => (
                $playerEvent->getModifier()->getTarget() === ModifierTargetEnum::HEALTH_POINT &&
                intval($playerEvent->getModifier()->getDelta()) === -2
            ))
            ->once()
        ;

        $this->cycleHandler->handleNewCycle($status, $daedalus, new \DateTime());

        $this->assertEquals($daedalusHull - 2, $daedalus->getHull());
    }
}
