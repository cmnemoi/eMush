<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\OpenCapsule;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;

class OpenCapsuleActionTest extends AbstractActionTest
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::BUILD);

        $this->action = new OpenCapsule(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
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
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $gameSpaceCapsule = new GameEquipment($room);
        $spaceCapsule = new EquipmentConfig();
        $spaceCapsule->setEquipmentName(EquipmentEnum::COFFEE_MACHINE);
        $gameSpaceCapsule
            ->setEquipment($spaceCapsule)
            ->setName(EquipmentEnum::COFFEE_MACHINE)
        ;

        $spaceCapsule->setActions(new ArrayCollection([$this->actionEntity]));

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameSpaceCapsule);

        $gameMetalScrap = new GameItem(new Place());
        $metalScrap = new ItemConfig();
        $metalScrap
            ->setEquipmentName(ItemEnum::METAL_SCRAPS)
        ;
        $gameMetalScrap
        ->setEquipment($metalScrap)
            ->setName(ItemEnum::METAL_SCRAPS)
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->andReturn(ItemEnum::METAL_SCRAPS)
            ->once()
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(0, $player->getStatuses());
    }
}
