<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\Build;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;

class BuildActionTest extends AbstractActionTest
{
    private GearToolServiceInterface|Mockery\Mock $gearToolService;

    /* @var EquipmentFactoryInterface|Mockery\Mock */
    private EquipmentFactoryInterface|Mockery\Mock $gameEquipmentService;

    protected AbstractAction $action;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);
        $this->gameEquipmentService = Mockery::mock(EquipmentFactoryInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Build(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->gearToolService,
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
        $gameItem = new Item();
        $item = new ItemConfig();
        $item->setName('blueprint');
        $gameItem
            ->setConfig($item)
            ->setHolder($room)
            ->setName('blueprint')
        ;

        $product = new ItemConfig();
        $product->setName('product');
        $gameProduct = new Item();
        $gameProduct
            ->setConfig($product)
            ->setName('product');

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients(['metal_scraps' => 1])
            ->setEquipment($product);
        $item->setMechanics(new ArrayCollection([$blueprint]));

        $gameIngredient = new Item();
        $ingredient = new ItemConfig();
        $ingredient->setName('metal_scraps');
        $gameIngredient
            ->setConfig($ingredient)
            ->setHolder($room)
            ->setName('metal_scraps')
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameIngredient]))->once();

        $this->gameEquipmentService->shouldReceive('createGameEquipment')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->times(3);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
