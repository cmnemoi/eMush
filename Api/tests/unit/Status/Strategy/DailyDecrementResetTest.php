<?php

declare(strict_types=1);

namespace Mush\tests\unit\Status\Strategy;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\ChargeStrategies\DailyDecrementReset;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DailyDecrementResetTest extends TestCase
{
    private DailyDecrementReset $strategy;
    private FakeStatusService $statusService;
    private ChargeStatus $mushStatus;

    /**
     * @before
     */
    public function before()
    {
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
        $this->statusService = new FakeStatusService();
        $this->strategy = new DailyDecrementReset($this->statusService);
        $this->mushStatus = StatusFactory::createChargeStatusFromStatusName(PlayerStatusEnum::MUSH, $player);
    }

    public function testShouldResetChargeToMinValue(): void
    {
        $this->mushStatus->setCharge(1);

        $this->strategy->execute($this->mushStatus, [EventEnum::NEW_DAY], new \DateTime());

        self::assertEquals(0, $this->mushStatus->getCharge());
    }

    public function testShouldResetOnlyOnNewDay(): void
    {
        $this->mushStatus->setCharge(1);

        $this->strategy->execute($this->mushStatus, [EventEnum::NEW_CYCLE], new \DateTime());

        self::assertEquals(1, $this->mushStatus->getCharge());
    }
}
