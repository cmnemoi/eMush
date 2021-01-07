<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\WaterPlant;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class WaterPlantActionTest extends AbstractActionTest
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

        $this->actionEntity = $this->createActionEntity(ActionEnum::WATER_PLANT, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new WaterPlant(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $this->statusService
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
        $room = new Room();

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $plant = new Plant();

        $thirsty = new Status();
        $thirsty
            ->setName(EquipmentStatusEnum::PLANT_THIRSTY)
        ;

        $gameItem->addStatus($thirsty);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //Not a plant
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $item->setMechanics(new ArrayCollection([$plant]));

        //Not thirsty
        $gameItem->removeStatus($thirsty);
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
              ->setEquipment($item)
              ->setRoom($room)
        ;

        $plant = new Plant();
        $item->setMechanics(new ArrayCollection([$plant]));

        $thirsty = new Status();
        $thirsty
            ->setName(EquipmentStatusEnum::PLANT_THIRSTY)
        ;

        $gameItem->addStatus($thirsty);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());

        $driedOut = new Status();
        $driedOut
            ->setName(EquipmentStatusEnum::PLANT_DRIED_OUT)
        ;

        $gameItem->removeStatus($thirsty);
        $gameItem->addStatus($driedOut);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
    }
}
