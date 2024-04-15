<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\PublicBroadcast;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class PublicBroadcastActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::PUBLIC_BROADCAST);
        $this->actionEntity->setOutputQuantity(3);

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new PublicBroadcast(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecuteAlreadyWatched()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item)->setName('equipment');

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $alienTVConfig = new ChargeStatusConfig();
        $alienTVConfig->setStatusName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST);
        $alienTVStatus = new ChargeStatus($player, $alienTVConfig);
        $alienTVStatus
            ->setCharge(1);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->eventService->shouldReceive('callEvent')->never();

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item)->setName('equipment');

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(2)
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
