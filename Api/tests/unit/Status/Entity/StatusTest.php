<?php

namespace Mush\Test\Status\Entity;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function testAddStatus()
    {
        $player = new Player();

        $status = new Status($player);

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
        $equipment = new GameEquipment();

        $status = new Status($equipment);
        $player = new Player();

        $status->setTarget($player);

        $this->assertEquals($equipment, $status->getOwner());
        $this->assertEquals($player, $status->getTarget());
        $this->assertEquals(1, $equipment->getStatuses()->count());
        $this->assertEquals(1, $player->getTargetingStatuses()->count());

        $equipment->removeStatus($status);

        $this->assertEquals(0, $player->getStatuses()->count());
    }
}
