<?php

namespace Mush\Test\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Enum\RoomEnum;
use Mush\Room\Service\RoomServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusTest extends TestCase
{

    public function testRoom()
    {
        $daedalus1 = new Daedalus();
        $daedalus2 = new Daedalus();
        $room1 = new Room();
        $room2 = new Room();

        $daedalus1->addRoom($room1);

        $this->assertCount(1, $daedalus1->getRooms());
        $this->assertCount(0, $daedalus2->getRooms());
        $this->assertEquals($daedalus1, $room1->getDaedalus());
        $this->assertNull($room2->getDaedalus());

        $room1->setDaedalus($daedalus2);

        $this->assertCount(0, $daedalus1->getRooms());
        $this->assertCount(1, $daedalus2->getRooms());
        $this->assertEquals($daedalus2, $room1->getDaedalus());
        $this->assertNull($room2->getDaedalus());

        $daedalus2->addRoom($room1);

        $this->assertCount(0, $daedalus1->getRooms());
        $this->assertCount(1, $daedalus2->getRooms());
        $this->assertEquals($daedalus2, $room1->getDaedalus());
        $this->assertNull($room2->getDaedalus());

        $daedalus2->removeRoom($room1);

        $this->assertCount(0, $daedalus1->getRooms());
        $this->assertCount(0, $daedalus2->getRooms());
        $this->assertNull($room1->getDaedalus());
        $this->assertNull($room2->getDaedalus());
    }
}
