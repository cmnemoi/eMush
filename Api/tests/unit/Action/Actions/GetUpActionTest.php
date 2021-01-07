<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\GetUp;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class GetUpActionTest extends AbstractActionTest
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::GET_UP);

        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new GetUp(
            $this->eventDispatcher,
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
        $daedalus = new Daedalus();
        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);
        $player2 = $this->createPlayer($daedalus, $room);

        $gameItem = new GameEquipment();
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
        ;
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
            ->setName(EquipmentEnum::BED)
        ;

        $status = new Status();
        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setPlayer($player2)
            ->setGameEquipment($gameItem);

        $actionParameter = new ActionParameters();

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameEquipment();
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
        ;
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
            ->setName(EquipmentEnum::BED)
        ;

        $status = new Status();
        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setPlayer($player)
            ->setGameEquipment($gameItem);

        $actionParameter = new ActionParameters();

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
