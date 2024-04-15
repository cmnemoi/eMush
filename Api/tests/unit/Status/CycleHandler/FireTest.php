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
use Mush\Place\Enum\PlaceTypeEnum;
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

/**
 * @internal
 */
final class FireTest extends TestCase
{
    private Mockery\Mock|RandomServiceInterface $randomService;

    private EventServiceInterface|Mockery\Mock $eventService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    private DaedalusServiceInterface|Mockery\Mock $daedalusService;
    private Mockery\Mock|StatusServiceInterface $statusService;
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
     * @covers \Mush\Status\CycleHandler\Fire::fireDamage
     * @covers \Mush\Status\CycleHandler\Fire::handleNewCycle
     * @covers \Mush\Status\CycleHandler\Fire::propagateFire
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
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player
            ->setPlayerVariables($characterConfig)
            ->setPlayerInfo($playerInfo);
        $room->addPlayer($player);

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->twice();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')->andReturn(2)->twice();
        $this->randomService->shouldReceive('getRandomElements')->andReturn([$room])->once();
        $this->daedalusService->shouldReceive('persist')->once();

        self::assertTrue($room->hasStatus(StatusEnum::FIRE));

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (PlayerVariableEvent $playerEvent, string $eventName) => (
                $playerEvent->getRoundedQuantity() === -2
                && $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $playerEvent->getVariableName() === PlayerVariableEnum::HEALTH_POINT
            ))
            ->once();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (DaedalusVariableEvent $daedalusEvent, string $eventName) => (
                $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $daedalusEvent->getVariableName() === DaedalusVariableEnum::HULL
            ))
            ->once();

        $this->cycleHandler->handleNewCycle($status, $room, $date);
        self::assertSame($daedalusHull, $daedalus->getHull());
    }

    /**
     * @dataProvider provideFirePropagationCases
     */
    public function testFirePropagation(int $roomNumbers, int $doorPerRoom, int $numberOfFires, int $expectedNumberOfFires, int $expectedDispatchedEvents): void
    {
        \assert($roomNumbers >= $numberOfFires);

        $date = new \DateTime();

        /** @var ArrayCollection<array-key, Place> $rooms */
        $rooms = new ArrayCollection();

        /** @var ArrayCollection<array-key, Door> $doors */
        $doors = new ArrayCollection();

        for ($i = 0; $roomNumbers !== $i; ++$i) {
            $rooms->add((new Place())->setName("Place {$i}"));
        }
        // For each room, we add the requested doors
        foreach ($rooms as $index => $room) {
            for ($i = 0; $doorPerRoom !== $i; ++$i) {
                // We take the room from our index + 1
                $roomsSegment = new ArrayCollection($rooms->slice($index, $doorPerRoom === 1 ? $doorPerRoom + 1 : $doorPerRoom));
                if ($roomsSegment->count() <= 1) {
                    continue;
                }

                $doors->add((new Door($room))->setRooms($roomsSegment));
            }
        }

        $expectedCont = ($doorPerRoom * $roomNumbers) - $doorPerRoom;
        self::assertCount($roomNumbers, $rooms);
        self::assertCount($expectedCont, $doors);
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

        $roomsInFire = $rooms->filter(static fn (Place $place) => $place->hasStatus(StatusEnum::FIRE));
        $roomsNotInFire = $rooms->filter(static fn (Place $place) => !$place->hasStatus(StatusEnum::FIRE));

        if ($numberOfFires < $roomNumbers) {
            self::assertCount($roomNumbers - $numberOfFires, $roomsNotInFire);
            $roomsNotInFire->forAll(static fn (int $_, Place $place) => self::assertFalse($place->hasStatus(StatusEnum::FIRE)));
        }

        self::assertCount($numberOfFires, $roomsInFire);
        self::assertCount($roomNumbers - $numberOfFires, $roomsNotInFire);

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->atLeast()->once();
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')->andReturn(2)->once();
        $this->randomService->shouldReceive('getRandomElements')->andReturn($roomsInFire->toArray())->once();
        $this->randomService->shouldReceive('getRandomElement')->andReturn($roomsNotInFire?->first() ?: null)
            ->atMost()->times($numberOfFires === $roomNumbers ? 0 : 1);
        $this->gameEquipmentService->shouldReceive('handleBreakFire')->andReturns()->atLeast()->once();
        $this->daedalusService->shouldReceive('persist')->once();

        $this->statusService
            ->shouldReceive('createStatusFromName')
            ->withArgs(static fn (string $name, StatusHolderInterface $holder, array $tags, \DateTime $dateTime) => (
                $name === StatusEnum::FIRE
                && $holder === $roomsNotInFire->first()
                && $tags === [RoomEventEnum::PROPAGATING_FIRE]
                && $dateTime === $date
            ))
            ->atMost()->times($expectedDispatchedEvents);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(static fn (DaedalusVariableEvent $daedalusEvent, string $eventName) => (
                $eventName === VariableEventInterface::CHANGE_VARIABLE
                && $daedalusEvent->getVariableName() === DaedalusVariableEnum::HULL
            ))
            ->once();

        $this->cycleHandler->handleNewCycle($statuses->first(), $rooms->first(), $date);
        // $this->assertCount($expectedNumberOfFires, $daedalus->getRooms()->filter(static fn(Place $place) => $place->hasStatus(StatusEnum::FIRE)));
        // TODO: How to count propagated fires?
    }

    /**
     * @covers \Mush\Status\CycleHandler\Fire::handleNewCycle
     *
     * @dataProvider provideFireDoesntGoSomewhereElseCases
     */
    public function testFireDoesntGoSomewhereElse(string $placeType): void
    {
        $date = new \DateTime();
        $roomNotFireCapable = (new Place())
            ->setType($placeType);

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
        $roomNotFireCapable->setDaedalus($daedalus);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setStatusName(StatusEnum::FIRE);
        $status = new ChargeStatus($roomNotFireCapable, $statusConfig);
        $status
            ->setCharge(1);

        $this->randomService->shouldReceive('isSuccessful')->never();
        $this->randomService->shouldReceive('getRandomElements')->never();
        $this->cycleHandler->handleNewCycle($status, $roomNotFireCapable, $date);
    }

    /**
     * [] = room
     * | = door = room - 1.
     *
     * F.E:
     * [ 🔥 ] | [] => will propagate
     * [ 🔥 ] | [ 🔥 ] | [] => will propagate
     * [ 🔥 ] | [ 🔥 ] | [ 🔥 ] | [ 🔥 ] => won't propagate
     *
     * @return iterable [number of rooms, number of door per room, number of fire, expected number of fires, number of dispatched events]
     */
    public static function provideFirePropagationCases(): iterable
    {
        yield [2, 1, 1, 2, 1];

        yield [3, 1, 2, 3, 2];

        yield [3, 1, 1, 2, 1];

        yield [4, 2, 1, 2, 1];

        yield [4, 2, 2, 3, 2];

        yield [4, 2, 3, 4, 3];

        yield [4, 2, 4, 4, 0];
    }

    /**
     * Ensure fire can only be propagated to rooms.
     */
    public static function provideFireDoesntGoSomewhereElseCases(): iterable
    {
        yield 'Space' => [PlaceTypeEnum::SPACE];

        yield 'PatrolShip' => [PlaceTypeEnum::PATROL_SHIP];

        yield 'Planet' => [PlaceTypeEnum::PLANET];
    }
}
