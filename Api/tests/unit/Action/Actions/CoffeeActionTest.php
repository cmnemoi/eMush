<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Coffee;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CoffeeActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Coffee(
            $this->eventDispatcher,
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

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(EquipmentStatusEnum::CHARGES)
             ->setCharge(1);

        $gameCoffeeMachine = new GameEquipment();
        $coffeeMachine = new EquipmentConfig();
        $coffeeMachine->setName(EquipmentEnum::COFFEE_MACHINE);
        $gameCoffeeMachine
            ->setEquipment($coffeeMachine)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
            ->setRoom(null)
            ->addStatus($chargeStatus)
        ;

        $chargeStatus->setGameEquipment($gameCoffeeMachine);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameCoffeeMachine);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //No coffee Machine in the room
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        //Standard Ration
        $room = new Room();

        $chargeStatus = new ChargeStatus();
        $chargeStatus
             ->setName(EquipmentStatusEnum::CHARGES)
             ->setCharge(1);

        $gameCoffeeMachine = new GameEquipment();
        $coffeeMachine = new EquipmentConfig();
        $coffeeMachine->setName(EquipmentEnum::COFFEE_MACHINE);
        $gameCoffeeMachine
            ->setEquipment($coffeeMachine)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
            ->setRoom($room)
            ->addStatus($chargeStatus)
        ;
        $coffeeMachine->setActions(new ArrayCollection([$this->actionEntity]));
        $chargeStatus->setGameEquipment($gameCoffeeMachine);

        $player = $this->createPlayer(new Daedalus(), $room);

        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameCoffeeMachine);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $gameCoffee = new GameItem();
        $coffee = new ItemConfig();
        $coffee
             ->setName(GameRationEnum::COFFEE)
         ;
        $gameCoffee
            ->setEquipment($coffee)
            ->setName(GameRationEnum::COFFEE)
        ;

        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gameCoffee)->once();
        $this->gameEquipmentService->shouldReceive('isOperational')->andReturn(true)->once();
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->gameEquipmentService->shouldReceive('persist');
        $this->statusService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(0, $room->getEquipments()->first()->getStatuses()->first()->getCharge());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
