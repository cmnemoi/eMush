<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\PublicBroadcast;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
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

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $alienTVConfig = new ChargeStatusConfig();
        $alienTVConfig->setStatusName(PlayerStatusEnum::WATCHED_PUBLIC_BROADCAST);
        $alienTVStatus = new ChargeStatus($player, $alienTVConfig);
        $alienTVStatus
            ->setCharge(1)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        // @TODO : fix me
        // $this->eventService->shouldReceive('callEvent')->twice();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
