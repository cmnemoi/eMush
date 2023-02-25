<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ScrewTalkie;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class ScrewTalkieActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SCREW_TALKIE, 2);

        $this->action = new ScrewTalkie(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($targetPlayer);
        $item = new ItemConfig();
        $gameItem
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($item)
        ;

        $mushStatus = new ChargeStatus($player, new ChargeStatusConfig());

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventDispatcher->shouldReceive('dispatch')->twice();
        // Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $targetPlayer->getEquipments());
    }

    public function testExecuteAlreadyBrokenTalkie()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($targetPlayer);
        $item = new ItemConfig();
        $gameItem
            ->setName(ItemEnum::ITRACKIE)
            ->setEquipment($item)
            ->setHolder($targetPlayer)
        ;

        $brokenConfig = new StatusConfig();
        $brokenConfig->setName(EquipmentStatusEnum::BROKEN);
        $brokenStatus = new Status($gameItem, $brokenConfig);

        $mushStatus = new ChargeStatus($player, new ChargeStatusConfig());

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        // Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $targetPlayer->getEquipments());
    }
}
