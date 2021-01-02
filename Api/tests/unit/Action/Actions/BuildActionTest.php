<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Build;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BuildActionTest extends TestCase
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    private GameConfig $gameConfig;
    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Build(
            $eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $gameConfigService
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
        $gameEquipment = new GameEquipment();
        $equipment = new EquipmentConfig();
        $equipment->setName('blueprint');
        $gameEquipment
                    ->setEquipment($equipment)
                    ->setRoom($room)
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
                 ->setRoom($room)
                 ->setName('metal_scraps');

        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameEquipment);
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($player, $actionParameter);

        //Not a blueprint
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $equipment->setMechanics(new ArrayCollection([$blueprint]));

        //Ingredient in another room
        $gameIngredient->setRoom(new Room());

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //Not enough of a given ingredient
        $gameIngredient->setRoom($room);
        $blueprint
               ->setIngredients(['metal_scraps' => 2]);
        $equipment->setMechanics(new ArrayCollection([$blueprint]));

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setName('blueprint');
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
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
                ->setRoom($room)
                ->setName('metal_scraps');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = new Player();
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($player, $actionParameter);

        $this->gameConfig->setMaxItemInInventory(3);
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $this->gameEquipmentService->shouldReceive('createGameEquipment')->andReturn($gameProduct)->once();
        $this->gameEquipmentService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getRoom()->getEquipments());
        $this->assertEquals($player->getItems()->first()->getEquipment(), $product);
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
