<?php

namespace Mush\Tests\unit\Daedalus\Service;

use Mockery;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusWidgetService;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DaedalusWidgetServiceTest extends TestCase
{
    /** @var AlertServiceInterface|Mockery\Mock */
    private AlertServiceInterface $alertService;

    /**
     * @before
     */
    public function before()
    {
        $this->alertService = \Mockery::mock(AlertServiceInterface::class);

        $this->service = new DaedalusWidgetService(
            $this->alertService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testgetMinimap()
    {
        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $room2 = new Place();
        $room2->setName(RoomEnum::BRIDGE);

        $daedalus = new Daedalus();
        $daedalus
            ->addPlace($room)
            ->addPlace($room2);

        $this->createSensorProjectsForDaedalus($daedalus);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $tracker = new GameItem($player);
        $tracker->setName(ItemEnum::TRACKER);

        $room2->addPlayer($player);
        $player->getPlace($room2);

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_EQUIPMENTS, $daedalus)
            ->andReturn(null)
            ->once();
        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_DOORS, $daedalus)
            ->andReturn(null)
            ->once();
        $minimap = $this->service->getMinimap($daedalus, $player);

        self::assertIsArray($minimap);
        self::assertArrayHasKey(RoomEnum::LABORATORY, $minimap);
        self::assertSame(0, $minimap[RoomEnum::LABORATORY]['players_count']);
        self::assertArrayHasKey(RoomEnum::BRIDGE, $minimap);
        self::assertSame(1, $minimap[RoomEnum::BRIDGE]['players_count']);
    }

    public function testgetMinimapNoTracker()
    {
        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $room2 = new Place();
        $room2->setName(RoomEnum::BRIDGE);

        $daedalus = new Daedalus();
        $daedalus
            ->addPlace($room)
            ->addPlace($room2);

        $this->createSensorProjectsForDaedalus($daedalus);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);

        $room2->addPlayer($player);
        $player->getPlace($room2);

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_EQUIPMENTS, $daedalus)
            ->andReturn(null)
            ->once();
        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_DOORS, $daedalus)
            ->andReturn(null)
            ->once();
        $minimap = $this->service->getMinimap($daedalus, $player);

        self::assertIsArray($minimap);
        self::assertEmpty($minimap);
    }

    public function testGetMinimapWithReportedFires()
    {
        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $room2 = new Place();
        $room2->setName(RoomEnum::BRIDGE);
        $room3 = new Place();
        $room3->setName(RoomEnum::CENTRAL_CORRIDOR);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setPlayerInfo($playerInfo);
        $player->setPlace($room3);

        $tracker = new GameItem($player);
        $tracker->setName(ItemEnum::TRACKER);

        $daedalus = new Daedalus();
        $daedalus
            ->addPlace($room)
            ->addPlace($room2)
            ->addPlace($room3);

        $this->createSensorProjectsForDaedalus($daedalus);

        $fireConfig = new StatusConfig();
        $fireConfig->setStatusName(StatusEnum::FIRE);

        $fire1 = new Status($room, $fireConfig);
        $fire2 = new Status($room2, $fireConfig);

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->andReturn(null)
            ->twice();
        $this->alertService->shouldReceive('isFireReported')
            ->with($room)
            ->andReturn(true)
            ->once();
        $this->alertService->shouldReceive('isFireReported')
            ->with($room2)
            ->andReturn(false)
            ->once();

        $minimap = $this->service->getMinimap($daedalus, $player);

        self::assertIsArray($minimap);
        // fire reported
        self::assertArrayHasKey(RoomEnum::LABORATORY, $minimap);
        self::assertTrue($minimap[RoomEnum::LABORATORY]['fire']);
        // fire but no reported
        self::assertArrayHasKey(RoomEnum::BRIDGE, $minimap);
        self::assertFalse($minimap[RoomEnum::BRIDGE]['fire']);
        // no fire
        self::assertArrayHasKey(RoomEnum::CENTRAL_CORRIDOR, $minimap);
        self::assertFalse($minimap[RoomEnum::CENTRAL_CORRIDOR]['fire']);
    }

    private function createSensorProjectsForDaedalus(Daedalus $daedalus): array
    {
        $projects = [];
        $projects[] = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::FIRE_SENSOR, $daedalus);
        $projects[] = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::DOOR_SENSOR, $daedalus);
        $projects[] = ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::EQUIPMENT_SENSOR, $daedalus);

        return $projects;
    }
}
