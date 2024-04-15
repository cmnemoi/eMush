<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\ExtractSpore;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;

/**
 * @internal
 */
final class ExtractSporeActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::EXTRACT_SPORE, 2);

        $this->action = new ExtractSpore(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setDailySporeNb(1)
            ->setInitOxygen(1)
            ->setInitFuel(1)
            ->setInitHull(1)
            ->setInitShield(1);
        $daedalus = new Daedalus();
        $daedalus->setDaedalusVariables($daedalusConfig);

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $player->setSpores(1);

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setStatusName(PlayerStatusEnum::MUSH);
        $mushStatus = new ChargeStatus($player, $mushConfig);
        $mushStatus
            ->setCharge(1);

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->times(2);

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $player->getStatuses());
    }
}
