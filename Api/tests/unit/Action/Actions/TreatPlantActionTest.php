<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\TreatPlant;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class TreatPlantActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TREAT_PLANT, 2);

        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new TreatPlant(
            $this->eventService,
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
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem
              ->setEquipment($item)
        ;

        $plant = new Plant();
        $plant->addAction($this->actionEntity);
        $item->setMechanics(new ArrayCollection([$plant]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
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
