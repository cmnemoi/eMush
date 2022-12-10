<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Disassemble;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;

class DisassembleActionTest extends AbstractActionTest
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    /* @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::DISASSEMBLE, 3);

        $this->action = new Disassemble(
            $this->eventDispatcher,
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

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setName('some name')
        ;

        $item
            ->setActions(new ArrayCollection([$this->actionEntity]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->never();

        // Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setName('some name')
        ;

        $item
            ->setActions(new ArrayCollection([$this->actionEntity]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $scrap = new GameItem(new Place());

        $this->eventDispatcher->shouldReceive('dispatch')->once();

        // Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(0, $player->getStatuses());
    }
}
