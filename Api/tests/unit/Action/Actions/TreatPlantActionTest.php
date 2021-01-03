<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\TreatPlant;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TreatPlantActionTest extends TestCase
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private AbstractAction $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new TreatPlant(
            $eventDispatcher,
            $this->gameEquipmentService,
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

        $diseased = new Status();
        $diseased
            ->setName(EquipmentStatusEnum::PLANT_DISEASED)
        ;

        $gameItem->addStatus($diseased);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($player, $actionParameter);

        //Not a plant
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $item->setMechanics(new ArrayCollection([$plant]));

        //Not thirsty
        $gameItem->removeStatus($diseased);
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

        $diseased = new Status();
        $diseased
            ->setName(EquipmentStatusEnum::PLANT_DISEASED)
        ;

        $gameItem->addStatus($diseased);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($player, $actionParameter);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(8, $player->getActionPoint());
    }

    private function createPlayer(Daedalus $daedalus, Room $room): Player
    {
        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setRoom($room)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;

        return $player;
    }
}
