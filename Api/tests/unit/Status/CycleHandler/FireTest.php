<?php

namespace Mush\Test\Status\CycleHandler;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\CycleHandler\Fire;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FireTest extends TestCase
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    private Mockery\Mock|EventDispatcherInterface $eventDispatcher;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    private DaedalusServiceInterface|Mockery\Mock $daedalusService;
    private Fire $cycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->daedalusService = \Mockery::mock(DaedalusServiceInterface::class);

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
        \Mockery::close();
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
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $room->setDaedalus($daedalus);

        $daedalusHull = 100;
        $daedalus->setHull($daedalusHull);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::FIRE);
        $status = new ChargeStatus($room, $statusConfig);
        $status
            ->setCharge(1)
        ;

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player
            ->setPlayerInfo($playerInfo)
        ;
        $room->addPlayer($player);

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaArray')->andReturn(2)->twice();
        $this->daedalusService->shouldReceive('persist')->once();

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (PlayerVariableEvent $playerEvent, string $eventName) => (
                intval($playerEvent->getQuantity()) === -2 &&
                $eventName === AbstractQuantityEvent::CHANGE_VARIABLE &&
                $playerEvent->getModifiedVariable() === PlayerVariableEnum::HEALTH_POINT
            ))
            ->once()
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (DaedalusModifierEvent $daedalusEvent, string $eventName) => (
                $eventName === AbstractQuantityEvent::CHANGE_VARIABLE &&
                $daedalusEvent->getModifiedVariable() === DaedalusVariableEnum::HULL
            ))
            ->once()
        ;

        $this->cycleHandler->handleNewCycle($status, $room, new \DateTime());

        $this->assertEquals($daedalusHull, $daedalus->getHull());
    }
}
