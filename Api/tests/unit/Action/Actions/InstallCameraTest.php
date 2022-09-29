<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\InstallCamera;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentFactoryInterface;
use Mush\Place\Entity\Place;

class InstallCameraActionTest extends AbstractActionTest
{
    private EquipmentFactoryInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INSTALL_CAMERA);
        $this->gameEquipmentService = Mockery::mock(EquipmentFactoryInterface::class);

        $this->action = new InstallCamera(
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

        $cameraItem = new Item();
        $cameraItemConfig = new ItemConfig();
        $cameraItemConfig->setName(EquipmentEnum::COFFEE_MACHINE);
        $cameraItem
            ->setConfig($cameraItemConfig)
            ->setName(ItemEnum::CAMERA_ITEM)
            ->setHolder($room)
        ;

        $cameraItemConfig->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $cameraItem);

        $cameraEquipment = new Equipment();
        $cameraEquipmentConfig = new ItemConfig();
        $cameraEquipmentConfig
            ->setName(GameRationEnum::COFFEE)
        ;
        $cameraEquipment
            ->setConfig($cameraEquipmentConfig)
            ->setName(GameRationEnum::COFFEE)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
