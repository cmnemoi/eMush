<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\InstallCamera;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;

class InstallCameraTest extends AbstractActionTest
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::INSTALL_CAMERA);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->action = new InstallCamera(
            $this->eventService,
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
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $cameraItem = new GameItem($room);
        $cameraItemConfig = new ItemConfig();
        $cameraItemConfig->setEquipmentName(EquipmentEnum::COFFEE_MACHINE);
        $cameraItem
            ->setEquipment($cameraItemConfig)
            ->setName(ItemEnum::CAMERA_ITEM)
        ;

        $cameraItemConfig->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $cameraItem);

        $cameraEquipment = new GameEquipment(new Place());
        $cameraEquipmentConfig = new ItemConfig();
        $cameraEquipmentConfig
            ->setEquipmentName(GameRationEnum::COFFEE)
        ;
        $cameraEquipment
            ->setEquipment($cameraEquipmentConfig)
            ->setName(GameRationEnum::COFFEE)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
