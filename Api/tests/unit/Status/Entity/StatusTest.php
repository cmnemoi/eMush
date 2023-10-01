<?php

namespace Mush\Tests\unit\Status\Entity;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function testAddStatus()
    {
        $player = new Player();

        $statusConfig = new StatusConfig();
        $status = new Status($player, $statusConfig);

        $this->assertEquals($player, $status->getOwner());
        $this->assertEquals(1, $player->getStatuses()->count());

        $player->addStatus($status);

        $this->assertEquals($player, $status->getOwner());
        $this->assertEquals(1, $player->getStatuses()->count());

        $player->removeStatus($status);

        $this->assertEquals(0, $player->getStatuses()->count());
    }

    public function testAddStatusWithTarget()
    {
        $equipment = new GameEquipment(new Place());

        $statusConfig = new StatusConfig();
        $status = new Status($equipment, $statusConfig);
        $player = new Player();

        $status->setTarget($player);

        $this->assertEquals($equipment, $status->getOwner());
        $this->assertEquals($player, $status->getTarget());
        $this->assertEquals(1, $equipment->getStatuses()->count());
        $this->assertEquals(1, $player->getTargetingStatuses()->count());

        $equipment->removeStatus($status);

        $this->assertNull($status->getStatusTargetTarget());
        $this->assertEquals(0, $player->getStatuses()->count());
    }

    public function testAddRoomStatus()
    {
        $room = new Place();

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setChargeStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setChargeVisibility(VisibilityEnum::PUBLIC)
            ->setAutoRemove(false)
            ->setStatusName('status')
        ;
        $status = new ChargeStatus($room, $statusConfig);
        $status
            ->setCharge(0)
        ;

        $status->getOwner();

        $this->assertEquals($room, $status->getOwner());
        $this->assertEquals(1, $room->getStatuses()->count());

        $room->removeStatus($status);

        $this->assertEquals(0, $room->getStatuses()->count());
    }
}
