<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\Build;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class BuildActionTest extends AbstractActionTest
{
    protected AbstractAction $actionHandler;
    private GearToolServiceInterface|Mockery\Mock $gearToolService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->createActionEntity(ActionEnum::BUILD);

        $this->actionHandler = new Build(
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
            ->setName('blueprint');

        $product = new ItemConfig();
        $product->setEquipmentName('product');
        $gameProduct = new GameItem(new Place());
        $gameProduct
            ->setEquipment($product)
            ->setName('product');

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients(['metal_scraps' => 1])
            ->setCraftedEquipmentName($product->getEquipmentName());
        $item->setMechanics(new ArrayCollection([$blueprint]));

        $gameIngredient = new GameItem($room);
        $ingredient = new ItemConfig();
        $ingredient->setEquipmentName('metal_scraps');
        $gameIngredient
            ->setEquipment($ingredient)
            ->setName('metal_scraps');

        $player = $this->createPlayer($daedalus, $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameIngredient]))->once();

        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->eventService->shouldReceive('callEvent')->times(2);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
