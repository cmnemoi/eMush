<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ShowerActionTest extends AbstractActionTest
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
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::SHOWER, 2);

        $this->action = new Shower(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->statusService,
            $this->playerService
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

        $gameItem = new GameEquipment();
        $item = new EquipmentConfig();
        $gameItem
            ->setEquipment($item)
            ->setPlace($room)
        ;

        $action = new Action();
        $action->setName(ActionEnum::SHOWER);
        $item->setActions(new ArrayCollection([$action]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $dirty = new Status($player);
        $dirty
            ->setName(PlayerStatusEnum::DIRTY)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameItem);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->gameEquipmentService->shouldReceive('isOperational')->andReturn(true)->once();
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(8, $player->getActionPoint());
    }
}
