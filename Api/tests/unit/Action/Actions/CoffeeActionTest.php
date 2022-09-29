<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Coffee;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

class CoffeeActionTest extends AbstractActionTest
{
    private StatusServiceInterface|Mockery\Mock $statusService;

    private EquipmentFactoryInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->gameEquipmentService = Mockery::mock(EquipmentFactoryInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Coffee(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService
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

        $gameCoffeeMachine = new Equipment();
        $coffeeMachine = new EquipmentConfig();
        $coffeeMachine->setName(EquipmentEnum::COFFEE_MACHINE);
        $gameCoffeeMachine
            ->setConfig($coffeeMachine)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
            ->setHolder($room)
        ;

        $coffeeMachine->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameCoffeeMachine);

        $gameCoffee = new Item();
        $coffee = new ItemConfig();
        $coffee
             ->setName(GameRationEnum::COFFEE)
        ;
        $gameCoffee
            ->setConfig($coffee)
            ->setName(GameRationEnum::COFFEE)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
