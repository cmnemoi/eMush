<?php

namespace Mush\Test\Place\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Event\RoomEvent;
use Mush\Place\Service\RoomEventService;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RoomEventServiceTest extends TestCase
{
    private RoomEventService $roomEventService;

    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->roomEventService = new RoomEventService(
            $this->randomService,
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

    public function testHandleNewFire()
    {
        $date = new \DateTime();
        $room = $this->createRoom();

        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->roomEventService->handleNewFire($room, $date);

        $this->assertCount(0, $room->getStatuses());

        $date = new \DateTime();
        $room = $this->createRoom();

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(
                fn (RoomEvent $roomEvent, string $name) => ($roomEvent->getRoom() === $room && $name === RoomEvent::STARTING_FIRE))
            ->once()
        ;

        $this->roomEventService->handleNewFire($room, $date);
    }

    public function testHandleNewFireWithAlreadyAFire()
    {
        $date = new \DateTime();
        $room = $this->createRoom();

        $fireStatus = new ChargeStatus($room);
        $fireStatus
            ->setName(StatusEnum::FIRE)
            ->setCharge(0)
        ;

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->never();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->withArgs(
                fn (RoomEvent $roomEvent, string $name) => ($roomEvent->getRoom() === $room && $name === RoomEvent::STARTING_FIRE))
            ->never()
        ;

        $this->roomEventService->handleNewFire($room, $date);
    }

    private function createRoom(): Place
    {
        $room = new Place();

        $daedalus = new Daedalus();
        $gameConfig = new GameConfig();
        $difficultyConfig = new DifficultyConfig();

        $gameConfig->setDifficultyConfig($difficultyConfig);
        $daedalus->setGameConfig($gameConfig);

        $room->setDaedalus($daedalus);

        return $room;
    }
}
