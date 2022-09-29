<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Transplant;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;

class PlantActionTest extends AbstractActionTest
{
    private GearToolServiceInterface|Mockery\Mock $gearToolService;

    private EquipmentFactoryInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TRANSPLANT, 1);
        $this->gameEquipmentService = Mockery::mock(EquipmentFactoryInterface::class);
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);

        $this->action = new Transplant(
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
        $gameItem
                    ->setConfig($item)
                    ->setHolder($room)
                    ->setName('toto')
        ;

        $fruit = new Fruit();
        $fruit->addAction($this->actionEntity);
        $fruit->setPlantName('banana_tree');

        $item->setMechanics(new ArrayCollection([$fruit]));

        $plant = new ItemConfig();
        $plant->setName('banana_tree');
        $gamePlant = new Item();
        $gamePlant
            ->setConfig($plant)
            ->setName('banana_tree')
        ;

        $gameHydropot = new Item();
        $hydropot = new ItemConfig();
        $hydropot->setName(ItemEnum::HYDROPOT);
        $gameHydropot
                    ->setConfig($hydropot)
                    ->setHolder($room)
                    ->setName(ItemEnum::HYDROPOT)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameHydropot]));
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->times(3);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getEquipments());
    }
}
