<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Phagocyte;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class PhagocyteTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->createActionEntity(ActionEnum::PHAGOCYTE);
        $this->actionHandler = new Phagocyte(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setStatusName(PlayerStatusEnum::MUSH);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $mushStatus->setCharge(1);

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig->setStatusName(PlayerStatusEnum::SPORES);
        $sporeStatus = new ChargeStatus($player, $sporeConfig);
        $sporeStatus->setCharge(1);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->times(3);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(2, $player->getStatuses());
    }
}
