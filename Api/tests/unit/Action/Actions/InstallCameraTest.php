<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\InstallCamera;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Entity\Place;

class InstallCameraActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INSTALL_CAMERA);

        $this->action = new InstallCamera(
            $this->eventService,
            $this->actionService,
            $this->validator,
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

        $cameraItem = new GameItem();
        $cameraItemConfig = new ItemConfig();
        $cameraItemConfig->setName(EquipmentEnum::COFFEE_MACHINE);
        $cameraItem
            ->setEquipment($cameraItemConfig)
            ->setName(ItemEnum::CAMERA_ITEM)
            ->setHolder($room)
        ;

        $cameraItemConfig->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $cameraItem);

        $cameraEquipment = new GameEquipment();
        $cameraEquipmentConfig = new ItemConfig();
        $cameraEquipmentConfig
            ->setName(GameRationEnum::COFFEE)
        ;
        $cameraEquipment
            ->setEquipment($cameraEquipmentConfig)
            ->setName(GameRationEnum::COFFEE)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
