<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Disassemble;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;

/**
 * @internal
 */
final class DisassembleActionTest extends AbstractActionTest
{
    private Mockery\Mock|RandomServiceInterface $randomService;

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

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setName('some name');

        $item
            ->setActions(new ArrayCollection([$this->actionEntity]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1]);

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(10)
            ->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL)
            ->never();
        $this->randomService->shouldReceive('isActionSuccessful')->with(10)->andReturn(false)->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->never();

        // Fail try
        $result = $this->action->execute();

        self::assertInstanceOf(Fail::class, $result);
        self::assertCount(1, $room->getEquipments());
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setName('some name');

        $item
            ->setActions(new ArrayCollection([$this->actionEntity]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1]);

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(10)
            ->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL)
            ->andReturn(0)
            ->once();
        $this->randomService->shouldReceive('isActionSuccessful')->with(10)->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->with(0)->andReturn(false)->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $scrap = new GameItem(new Place());

        $this->eventService->shouldReceive('callEvent')->once();

        // Success
        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(0, $player->getStatuses());
    }
}
