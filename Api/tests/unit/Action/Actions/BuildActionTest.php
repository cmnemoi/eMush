<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\Build;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BuildActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    protected AbstractAction $action;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new Build(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService
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
        $room = new Place();
        $gameEquipment = new GameEquipment();
        $equipment = new EquipmentConfig();
        $equipment->setName('blueprint');
        $gameEquipment
            ->setEquipment($equipment)
            ->setPlace($room)
            ->setName('blueprint');

        $product = new ItemConfig();

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients(['metal_scraps' => 1])
            ->setEquipment($product);

        $gameIngredient = new GameItem();
        $ingredient = new ItemConfig();
        $ingredient->setName('metal_scraps');
        $gameIngredient
            ->setEquipment($ingredient)
            ->setPlace($room)
            ->setName('metal_scraps');

        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameEquipment);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //Not a blueprint
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $equipment->setMechanics(new ArrayCollection([$blueprint]));

        //Ingredient in another room
        $gameIngredient->setPlace(new Place());

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //Not enough of a given ingredient
        $gameIngredient->setPlace($room);
        $blueprint
            ->setIngredients(['metal_scraps' => 2]);
        $equipment->setMechanics(new ArrayCollection([$blueprint]));

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
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
            ->setName('product');

        $blueprint = new Blueprint();
        $blueprint
            ->setIngredients(['metal_scraps' => 1])
            ->setEquipment($product);
        $item->setMechanics(new ArrayCollection([$blueprint]));

        $gameIngredient = new GameItem();
        $ingredient = new ItemConfig();
        $ingredient->setName('metal_scraps');
        $gameIngredient
            ->setEquipment($ingredient)
            ->setPlace($room)
            ->setName('metal_scraps');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $this->gameEquipmentService->shouldReceive('createGameEquipment')->andReturn($gameProduct)->once();

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
