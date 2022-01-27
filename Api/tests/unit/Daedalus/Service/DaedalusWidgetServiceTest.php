<?php

namespace Mush\Test\Daedalus\Service;

use Mockery;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusWidgetService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

class DaedalusWidgetServiceTest extends TestCase
{
    /** @var AlertServiceInterface|Mockery\Mock */
    private AlertServiceInterface $alertService;

    /**
     * @before
     */
    public function before()
    {
        $this->alertService = Mockery::mock(AlertServiceInterface::class);

        $this->service = new DaedalusWidgetService(
            $this->alertService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
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
            ->addPlace($room2)
        ;

        $player = new Player();
        $room2->addPlayer($player);

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_EQUIPMENTS, $daedalus)
            ->andReturn(null)
            ->once()
        ;
        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_DOORS, $daedalus)
            ->andReturn(null)
            ->once()
        ;
        $minimap = $this->service->getMinimap($daedalus);

        $this->assertIsArray($minimap);
        $this->assertArrayHasKey(RoomEnum::LABORATORY, $minimap);
        $this->assertEquals(0, $minimap[RoomEnum::LABORATORY]['players_count']);
        $this->assertArrayHasKey(RoomEnum::BRIDGE, $minimap);
        $this->assertEquals(1, $minimap[RoomEnum::BRIDGE]['players_count']);
    }

    public function testgetMinimapWithReportedFires()
    {
        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $room2 = new Place();
        $room2->setName(RoomEnum::BRIDGE);
        $room3 = new Place();
        $room3->setName(RoomEnum::CENTRAL_CORRIDOR);

        $player = new Player();

        $daedalus = new Daedalus();
        $daedalus
            ->addPlace($room)
            ->addPlace($room2)
            ->addPlace($room3)
        ;

        $fireConfig = new StatusConfig();
        $fireConfig->setName(StatusEnum::FIRE);

        $fire1 = new Status($room, $fireConfig);
        $fire2 = new Status($room2, $fireConfig);

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->andReturn(null)
            ->twice()
        ;
        $this->alertService->shouldReceive('isFireReported')
            ->with($room)
            ->andReturn(true)
            ->once()
        ;
        $this->alertService->shouldReceive('isFireReported')
            ->with($room2)
            ->andReturn(false)
            ->once()
        ;

        $minimap = $this->service->getMinimap($daedalus);

        $this->assertIsArray($minimap);
        //fire reported
        $this->assertArrayHasKey(RoomEnum::LABORATORY, $minimap);
        $this->assertTrue($minimap[RoomEnum::LABORATORY]['fire']);
        //fire but no reported
        $this->assertArrayHasKey(RoomEnum::BRIDGE, $minimap);
        $this->assertFalse($minimap[RoomEnum::BRIDGE]['fire']);
        //no fire
        $this->assertArrayHasKey(RoomEnum::CENTRAL_CORRIDOR, $minimap);
        $this->assertFalse($minimap[RoomEnum::CENTRAL_CORRIDOR]['fire']);
    }

    public function testgetMinimapWithReportedEquipments()
    {
        $room = new Place();
        $room->setName(RoomEnum::LABORATORY);
        $room2 = new Place();
        $room2->setName(RoomEnum::BRIDGE);

        $gameEquipment1 = new GameEquipment();
        $gameEquipment1->setHolder($room)->setName('equipment');
        $gameEquipment2 = new GameEquipment();
        $gameEquipment2->setHolder($room)->setName('equipment');

        $player = new Player();

        $daedalus = new Daedalus();
        $daedalus
            ->addPlace($room)
            ->addPlace($room2)
        ;

        $alert = new Alert();

        $alertElement1 = new AlertElement();
        $alertElement1->setEquipment($gameEquipment1)->setPlayer($player);
        $alertElement2 = new AlertElement();
        $alertElement2->setEquipment($gameEquipment2);

        $alert->addAlertElement($alertElement1)->addAlertElement($alertElement2);

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_EQUIPMENTS, $daedalus)
            ->andReturn($alert)
            ->once()
        ;
        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_DOORS, $daedalus)
            ->andReturn(null)
            ->once()
        ;

        $minimap = $this->service->getMinimap($daedalus);

        $this->assertIsArray($minimap);
        //1 equipment reported
        $this->assertArrayHasKey(RoomEnum::LABORATORY, $minimap);
        $this->assertEquals(1, $minimap[RoomEnum::LABORATORY]['broken_count']);
        //no equipment
        $this->assertArrayHasKey(RoomEnum::BRIDGE, $minimap);
        $this->assertEquals(0, $minimap[RoomEnum::BRIDGE]['broken_count']);
    }
}
