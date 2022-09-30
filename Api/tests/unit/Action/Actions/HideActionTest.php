<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Hide;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;

class HideActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::HIDE, 1);

        $this->action = new Hide(
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
        Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $daedalus = new Daedalus();
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem();

        $item = new ItemConfig();
        $item
            ->setActions(new ArrayCollection([$this->actionEntity]))
        ;
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setHolder($player)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventService
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof StatusEvent &&
                $event->getStatusName() === EquipmentStatusEnum::HIDDEN &&
                $event->getStatusHolder() === $gameItem &&
                $event->getStatusTarget() === $player
            )
            ->once()
        ;
        $this->eventService
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof InteractWithEquipmentEvent &&
                $event->getEquipment() === $gameItem &&
                $event->getActor() === $player &&
                $event->getReason() === ActionEnum::HIDE
            )
            ->once()
        ;

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
