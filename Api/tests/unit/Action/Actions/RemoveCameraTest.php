<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\RemoveCamera;
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

class RemoveCameraActionTest extends AbstractActionTest
{

    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REMOVE_CAMERA);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);

        $this->action = new RemoveCamera(
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

        $cameraEquipment = new GameEquipment();
        $cameraEquipmentConfig = new ItemConfig();
        $cameraEquipmentConfig
            ->setName(GameRationEnum::COFFEE)
        ;
        $cameraEquipment
            ->setEquipment($cameraEquipmentConfig)
            ->setName(GameRationEnum::COFFEE)
        ;

        $cameraEquipmentConfig->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player, $cameraEquipment);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('dispatch')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
