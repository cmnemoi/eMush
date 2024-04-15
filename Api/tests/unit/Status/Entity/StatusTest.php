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

/**
 * @internal
 */
final class StatusTest extends TestCase
{
    public function testAddStatus()
    {
        $player = new Player();

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName('status');
        $status = new Status($player, $statusConfig);

        self::assertSame($player, $status->getOwner());
        self::assertSame(1, $player->getStatuses()->count());

        $player->addStatus($status);

        self::assertSame($player, $status->getOwner());
        self::assertSame(1, $player->getStatuses()->count());

        $player->removeStatus($status);

        self::assertSame(0, $player->getStatuses()->count());
    }

    public function testAddStatusWithTarget()
    {
        $equipment = new GameEquipment(new Place());

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName('status');
        $status = new Status($equipment, $statusConfig);
        $player = new Player();

        $status->setTarget($player);

        self::assertSame($equipment, $status->getOwner());
        self::assertSame($player, $status->getTarget());
        self::assertSame(1, $equipment->getStatuses()->count());
        self::assertSame(1, $player->getTargetingStatuses()->count());

        $equipment->removeStatus($status);

        self::assertNull($status->getStatusTargetTarget());
        self::assertSame(0, $player->getStatuses()->count());
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
            ->setStatusName('status');
        $status = new ChargeStatus($room, $statusConfig);
        $status
            ->setCharge(0);

        $status->getOwner();

        self::assertSame($room, $status->getOwner());
        self::assertSame(1, $room->getStatuses()->count());

        $room->removeStatus($status);

        self::assertSame(0, $room->getStatuses()->count());
    }
}
