<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Extinguish;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ExtinguishActionTest extends AbstractActionTest
{
    /** @var PlaceServiceInterface | Mockery\Mock */
    private PlaceServiceInterface $placeService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REPAIR, 1);

        $this->placeService = Mockery::mock(PlaceServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new Extinguish(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->playerService,
            $this->randomService,
            $this->placeService,
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
        $fire = new Status($room);
        $fire
            ->setName(StatusEnum::FIRE)
        ;

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setPlace($room)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $this->placeService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer(new Daedalus(), $room);

        $attempt = new Attempt(new Player());
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction($this->action->getActionName())
        ;
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        //Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(1, $room->getStatuses());
        $this->assertEquals(1, $attempt->getCharge());

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        //Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $room->getStatuses());
    }
}
