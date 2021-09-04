<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Hide;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Event\AbstractMushEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusServiceInterface;

class HideActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::HIDE, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);

        $this->action = new Hide(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
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
            ->setPlayer($player)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('persist');

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (AbstractMushEvent $event) =>
                $event instanceof StatusEvent  &&
                $event->getStatusName() === EquipmentStatusEnum::HIDDEN &&
                $event->getStatusHolder() === $gameItem &&
                $event->getStatusTarget() === $player
            )
            ->once()
        ;

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getItems());
    }
}
