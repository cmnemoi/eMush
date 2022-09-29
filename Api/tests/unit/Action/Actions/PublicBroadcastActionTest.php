<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\PublicBroadcast;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Item;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;

class PublicBroadcastActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::PUBLIC_BROADCAST);

        $this->action = new PublicBroadcast(
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

        $gameItem = new Item();
        $item = new ItemConfig();
        $gameItem->setConfig($item);
        $gameItem
            ->setHolder($room)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $alienTVConfig = new ChargeStatusConfig();
        $alienTVConfig->setName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST);
        $alienTVStatus = new ChargeStatus($player, $alienTVConfig);
        $alienTVStatus
            ->setCharge(1)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        // @TODO : fix me
        // $this->eventDispatcher->shouldReceive('dispatch')->twice();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
