<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Transplant;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;

class PlantActionTest extends AbstractActionTest
{
    private GearToolServiceInterface|Mockery\Mock $gearToolService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TRANSPLANT, 1);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
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
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem
                    ->setEquipment($item)
                    ->setHolder($room)
                    ->setName('toto')
        ;

        $fruit = new Fruit();
        $fruit->addAction($this->actionEntity);
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
                    ->setHolder($room)
                    ->setName(ItemEnum::HYDROPOT)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->gearToolService->shouldReceive('getEquipmentsOnReachByName')->andReturn(new ArrayCollection([$gameHydropot]));
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->twice();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getEquipments());
    }
}
