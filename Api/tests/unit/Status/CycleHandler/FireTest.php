<?php

namespace Mush\Tests\unit\Status\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\Door;
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

    /**
     * @covers \Mush\Status\CycleHandler\Fire::handleNewCycle
     * @covers \Mush\Status\CycleHandler\Fire::propagateFire
     * @covers \Mush\Status\CycleHandler\Fire::fireDamage
     */
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
            ->setDaedalusConfig($daedalusConfig);
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $room->setDaedalus($daedalus);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::FIRE);
        $status = new ChargeStatus($room, $statusConfig);
        $status
            ->setCharge(1);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlayerVariables($characterConfig);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player
            ->setPlayerInfo($playerInfo);
        $room->addPlayer($player);

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->twice();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')->andReturn(2)->twice();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([$room])->once();
        $this->randomService->shouldReceive('getRandomElement')->andReturn($room)->once();
        $this->daedalusService->shouldReceive('persist')->once();

        $this->assertTrue($room->hasStatus(StatusEnum::FIRE));

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
            ->once();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (DaedalusVariableEvent $daedalusEvent, string $eventName) => (
                $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $daedalusEvent->getVariableName() === DaedalusVariableEnum::HULL
            ))
            ->once();

        $this->cycleHandler->handleNewCycle($status, $room, $date);
        $this->assertEquals($daedalusHull, $daedalus->getHull());
    }

    /**
     * @dataProvider fireTestPropagationDataProvider
     */
    public function testFirePropagation(int $roomNumbers, int $numberOfFires, int $expectedNumberOfFires, int $expectedDispatchedEvents): void
    {
        assert($roomNumbers >= $numberOfFires);

        $date = new \DateTime();
        $rooms = new ArrayCollection();

        for ($i = 0; $roomNumbers !== $i; ++$i) {
            $rooms->add(new Place());
        }

        $initialRoom = $rooms->first();
        $this->assertCount($roomNumbers, $rooms);

        $linkedDoor = (new Door($initialRoom))->setRooms($rooms);
        // Propagate all the fire 🔥.
        $difficultyConfig = (new DifficultyConfig())
            ->setPropagatingFireRate(100)
            ->setMaximumAllowedSpreadingFires(100);
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setMaxHull(100)
            ->setInitHull(100);

        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $gameConfig
            ->setDifficultyConfig($difficultyConfig)
            ->setDaedalusConfig($daedalusConfig);

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $rooms->forAll(static fn (int $_, Place $place) => $place->setDaedalus($daedalus));
        $daedalus->setDaedalusVariables($daedalusConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::FIRE);

        $statuses = new ArrayCollection();
        foreach ($rooms as $cleanRoom) {
            if ($statuses->count() === $numberOfFires) {
                break;
            }

            $charge = new ChargeStatus($cleanRoom, $statusConfig);
            $charge->setCharge(1);
            $statuses->add($charge);
        }

        if ($numberOfFires < $roomNumbers) {
            $remainingRooms = $rooms->filter(static fn (Place $place) => !$place->hasStatus(StatusEnum::FIRE));
            $this->assertCount($roomNumbers - $numberOfFires, $remainingRooms);
            $remainingRooms->forAll(fn (int $_, Place $place) => $this->assertFalse($place->hasStatus(StatusEnum::FIRE)));
        }

        $this->assertCount($roomNumbers, $linkedDoor->getRooms());
        $this->assertCount($numberOfFires, $rooms->filter(static fn (Place $place) => $place->hasStatus(StatusEnum::FIRE)));

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->atLeast()->once();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')->andReturn(2)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn($rooms->filter(static fn (Place $place) => $place->hasStatus(StatusEnum::FIRE))->toArray())->once();
        $this->randomService->shouldReceive('getRandomElement')->andReturn(
            $rooms->filter(static fn (Place $place) => !$place->hasStatus(StatusEnum::FIRE))?->first() ?: null
        )->atLeast()->once();
        $this->gameEquipmentService->shouldReceive('handleBreakFire')->andReturns()->once();
        $this->daedalusService->shouldReceive('persist')->once();

        $this->statusService
            ->shouldReceive('createStatusFromName')
            ->withArgs(static fn (string $name, StatusHolderInterface $holder, array $tags, \DateTime $dateTime) => (
                $name === StatusEnum::FIRE
                && $holder === $rooms->filter(static fn (Place $place) => !$place->hasStatus(StatusEnum::FIRE))->first()
                && $tags === [RoomEventEnum::PROPAGATING_FIRE]
                && $dateTime === $date
            ))
            ->times($expectedDispatchedEvents);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (DaedalusVariableEvent $daedalusEvent, string $eventName) => (
                $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $daedalusEvent->getVariableName() === DaedalusVariableEnum::HULL
            ))
            ->once();

        $this->cycleHandler->handleNewCycle($statuses->first(), $initialRoom, $date);
        // $this->assertCount($expectedNumberOfFires, $daedalus->getRooms()->filter(static fn(Place $place) => $place->hasStatus(StatusEnum::FIRE)));
        // TODO: How to count propagated fires?
    }

    /**
     * @return iterable [number of rooms, number of fire, expected number of fires, number of dispatched events]
     */
    final public static function fireTestPropagationDataProvider(): iterable
    {
        yield [2, 1, 2, 1];
        yield [3, 2, 3, 2];
        yield [3, 1, 2, 1];
        yield [4, 1, 2, 1];
        yield [4, 2, 3, 2];
        yield [4, 3, 4, 3];
        yield [4, 4, 4, 0];
    }
}
