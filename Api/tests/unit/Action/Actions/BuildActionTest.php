<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\Build;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;

class BuildActionTest extends AbstractActionTest
{
    private GearToolServiceInterface|Mockery\Mock $gearToolService;

    /* @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    protected AbstractAction $action;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Build(
            $this->eventService,
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
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $item->setEquipmentName('blueprint');
        $gameItem
            ->setEquipment($item)
            ->setName('blueprint')
        ;

        $product = new ItemConfig();
        $product->setEquipmentName('product');
        $gameProduct = new GameItem(new Place());
        $gameProduct
            ->setEquipment($product)
            ->setName('product')
        ;

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients(['metal_scraps' => 1])
            ->setCraftedEquipmentName($product->getEquipmentName())
        ;
        $item->setMechanics(new ArrayCollection([$blueprint]));

        $gameIngredient = new GameItem($room);
        $ingredient = new ItemConfig();
        $ingredient->setEquipmentName('metal_scraps');
        $gameIngredient
            ->setEquipment($ingredient)
            ->setName('metal_scraps')
        ;

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameIngredient]))->once();

        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->eventService->shouldReceive('callEvent')->times(2);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
