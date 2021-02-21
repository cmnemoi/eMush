<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Transplant;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlantActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var GearToolServiceInterface | Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TRANSPLANT, 1);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);

        $this->action = new Transplant(
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

    public function testCannotExecute()
    {
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
                    ->setEquipment($item)
                    ->setPlace($room)
                    ->setName('toto');

        $action = new Action();
        $action->setName(ActionEnum::TRANSPLANT);

        $fruit = new Fruit();
        $fruit->addAction($action);
        $fruit->setPlantName('banana_tree');

        $plant = new ItemConfig();
        $plant->setName('plant');

        $gameHydropot = new GameItem();
        $hydropot = new ItemConfig();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setEquipment($hydropot)
                    ->setPlace($room)
                    ->setName(ItemEnum::HYDROPOT)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $item->setMechanics(new ArrayCollection([$fruit]));
        //Hydropot in another room
        $gameHydropot->setPlace(new Place());
        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection());

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $item->setMechanics(new ArrayCollection([]));
        $gameHydropot->setPlace($room);
        //Not a fruit
        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameHydropot]));
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
                    ->setEquipment($item)
                    ->setPlace($room)
                    ->setName('toto');

        $action = new Action();
        $action->setName(ActionEnum::TRANSPLANT);

        $fruit = new Fruit();
        $fruit->addAction($action);
        $fruit->setPlantName('banana_tree');

        $item->setMechanics(new ArrayCollection([$fruit]));

        $plant = new ItemConfig();
        $plant->setName('banana_tree');
        $gamePlant = new GameItem();
        $gamePlant
            ->setEquipment($plant)
            ->setName('banana_tree')
        ;

        $gameHydropot = new GameItem();
        $hydropot = new ItemConfig();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setEquipment($hydropot)
                    ->setPlace($room)
                    ->setName(ItemEnum::HYDROPOT)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameHydropot]));
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gamePlant)->once();
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertContains($gamePlant, $player->getPlace()->getEquipments());
    }
}
