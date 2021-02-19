<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Hide;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class HideActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::HIDE, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new Hide(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->statusService,
            $this->playerService,
            $this->actionService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCannotExecute()
    {
        $room = new Place();

        $gameItem = new GameItem();
        $actionHide = new Action();
        $actionHide->setName(ActionEnum::HIDE);
        $item = new ItemConfig();
        $item
            ->setIsHideable(true)
            ->setActions(new ArrayCollection([$actionHide]))
        ;
        $gameItem
            ->setEquipment($item)
        ;

        $daedalus = new Daedalus();
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);
        $player = $this->createPlayer($daedalus, $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //item is not in the room
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //item is not hideable
        $gameItem->setPlace($room);
        $item->setIsHideable(false);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //ship isn't started
        $daedalus->setGameStatus(GameStatusEnum::STARTING);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
        $this->assertEquals(ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED, $this->action->cannotExecuteReason());
    }

    public function testExecute()
    {
        $room = new Place();

        $daedalus = new Daedalus();
        $daedalus->setGameStatus(GameStatusEnum::CURRENT);

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $actionHide = new Action();
        $actionHide->setName(ActionEnum::HIDE);
        $item = new ItemConfig();
        $item
            ->setIsHideable(true)
            ->setActions(new ArrayCollection([$actionHide]))
        ;
        $gameItem
            ->setName('itemName')
            ->setEquipment($item)
            ->setPlayer($player)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('createCoreStatus')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getItems());
    }
}
