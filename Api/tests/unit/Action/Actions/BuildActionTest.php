<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\Build;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BuildActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var GearToolServiceInterface | Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    protected AbstractAction $action;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Build(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $this->actionService,
            $this->gearToolService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testIsVisible()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients(['ItemEnum::METAL_SCRAPS' => 1])
            ->setEquipment(new ItemConfig())
        ;

        $gameEquipment = $this->createItem($room, 'product', $blueprint);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->assertTrue($this->action->isVisible());
    }

    public function testIsNotVisibleCannotReach()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients(['ItemEnum::METAL_SCRAPS' => 1])
            ->setEquipment(new ItemConfig())
        ;

        $gameEquipment = $this->createItem(new Place(), 'product', $blueprint);

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->assertFalse($this->action->isVisible());
    }

    public function testIsNotVisibleHasNotBlueprintMechanic()
    {
        $room = new Place();
        $player = $this->createPlayer(new Daedalus(), $room);

        $gameEquipment = $this->createItem(new Place(), 'product');

        $gameEquipment->getEquipment()->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $this->assertFalse($this->action->isVisible());
    }

    public function testExecute()
    {
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setName('blueprint');
        $gameItem
            ->setEquipment($item)
            ->setPlace($room)
            ->setName('blueprint')
        ;

        $product = new ItemConfig();
        $product->setName('product');
        $gameProduct = new GameItem();
        $gameProduct
            ->setEquipment($product)
            ->setName('product')
        ;

        $blueprint = new Blueprint();
        $blueprint
            ->setActions(new ArrayCollection([$this->actionEntity]))
            ->setIngredients(['metal_scraps' => 1])
            ->setEquipment($product)
        ;
        $item->setMechanics(new ArrayCollection([$blueprint]));

        $gameIngredient = new GameItem();
        $ingredient = new ItemConfig();
        $ingredient->setName('metal_scraps');
        $gameIngredient
            ->setEquipment($ingredient)
            ->setPlace($room)
            ->setName('metal_scraps')
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameIngredient]))->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipment')->andReturn($gameProduct)->once();

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }

    private function createItem(Place $place, string $name, ?Blueprint $blueprint = null): GameEquipment
    {
        $gameEquipment = new GameEquipment();
        $equipment = new EquipmentConfig();
        $equipment->setName($name);
        if ($blueprint) {
            $equipment->setMechanics(new ArrayCollection([$blueprint]));
        }
        $gameEquipment
            ->setName($name)
            ->setEquipment($equipment)
            ->setPlace($place)
        ;

        return $gameEquipment;
    }
}
