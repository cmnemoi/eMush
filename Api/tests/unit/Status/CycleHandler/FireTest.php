<?php

namespace Mush\Tests\unit\Status\CycleHandler;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEventEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\CycleHandler\Fire;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class FireTest extends TestCase
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    private Mockery\Mock|EventServiceInterface $eventService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    private DaedalusServiceInterface|Mockery\Mock $daedalusService;
    private StatusServiceInterface|Mockery\Mock $statusService;
    private Fire $cycleHandler;

    /**
     * @before
     */
    public function before(): void
    {
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->daedalusService = \Mockery::mock(DaedalusServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->cycleHandler = new Fire(
            $this->randomService,
            $this->eventService,
            $this->gameEquipmentService,
            $this->daedalusService,
            $this->statusService,
        );
    }

    /**
     * @after
     */
    public function after(): void
    {
        \Mockery::close();
    }

    public function testNewCycleFireDamage(): void
    {
        $date = new \DateTime();
        $room = new Place();

        $difficultyConfig = new DifficultyConfig();
        $daedalusConfig = new DaedalusConfig();
        $daedalusHull = 100;
        $daedalusConfig
            ->setMaxHull(100)
            ->setInitHull($daedalusHull);

        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $gameConfig
            ->setDifficultyConfig($difficultyConfig)
            ->setDaedalusConfig($daedalusConfig)
        ;
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $room->setDaedalus($daedalus);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::FIRE);
        $status = new ChargeStatus($room, $statusConfig);
        $status
            ->setCharge(1)
        ;

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player
            ->setPlayerInfo($playerInfo)
        ;
        $room->addPlayer($player);

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->twice();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')->andReturn(2)->twice();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([$room])->once();
        $this->randomService->shouldReceive('getRandomElement')->andReturn($room)->once();
        $this->daedalusService->shouldReceive('persist')->once();

        $this->statusService
            ->shouldReceive('createStatusFromName')
            ->withArgs(fn (string $name, StatusHolderInterface $holder, array $tags, \DateTime $dateTime) => (
                $name === StatusEnum::FIRE
                && $holder === $room
                && $tags === [RoomEventEnum::PROPAGATING_FIRE]
                && $dateTime === $date
            ))
            ->once();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (PlayerVariableEvent $playerEvent, string $eventName) => (
                $playerEvent->getRoundedQuantity() === -2
                && $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $playerEvent->getVariableName() === PlayerVariableEnum::HEALTH_POINT
            ))
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (DaedalusVariableEvent $daedalusEvent, string $eventName) => (
                $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $daedalusEvent->getVariableName() === DaedalusVariableEnum::HULL
            ))
            ->once()
        ;

        $this->cycleHandler->handleNewCycle($status, $room, $date);
        $this->assertEquals($daedalusHull, $daedalus->getHull());
    }
}
