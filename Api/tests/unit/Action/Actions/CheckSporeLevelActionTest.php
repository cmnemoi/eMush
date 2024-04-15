<?php

namespace Mush\Tests\unit\Action\Actions;

use Mush\Action\Actions\CheckSporeLevel;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;

/**
 * @internal
 */
final class CheckSporeLevelActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::CHECK_SPORE_LEVEL);

        $this->action = new CheckSporeLevel(
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
        $daedalus = new Daedalus();
        $room = new Place();

        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $sporeConfig = new ChargeStatusConfig();
        $sporeConfig->setStatusName(PlayerStatusEnum::SPORES);
        $sporeStatus = new ChargeStatus($player, $sporeConfig);
        $sporeStatus
            ->setCharge(1);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment->setName('equipment');

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
