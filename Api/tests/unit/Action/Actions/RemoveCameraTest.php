<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\RemoveCamera;
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

/**
 * @internal
 */
final class RemoveCameraTest extends AbstractActionTest
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::REMOVE_CAMERA);

        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->actionHandler = new RemoveCamera(
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
            ->setName(ItemEnum::CAMERA_ITEM);

        $cameraEquipment = new GameEquipment($room);
        $cameraEquipmentConfig = new ItemConfig();
        $cameraEquipmentConfig
            ->setEquipmentName(GameRationEnum::COFFEE);
        $cameraEquipment
            ->setEquipment($cameraEquipmentConfig)
            ->setName(GameRationEnum::COFFEE);

        $cameraEquipmentConfig->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $cameraEquipment);
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->once();
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
