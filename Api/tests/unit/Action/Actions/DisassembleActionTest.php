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
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Skill\Enum\SkillEnum;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->createActionEntity(ActionEnum::DISASSEMBLE, 3);

        $this->actionHandler = new Disassemble(
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
    protected function tearDown(): void
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
            ->setActionConfigs(new ArrayCollection([$this->actionConfig]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1]);

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS, $this->actionHandler->getTags())
            ->andReturn(10)
            ->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL, $this->actionHandler->getTags())
            ->never();
        $this->randomService->shouldReceive('isSuccessful')->with(10)->andReturn(false)->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->never();

        // Fail try
        $result = $this->actionHandler->execute();

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
            ->setActionConfigs(new ArrayCollection([$this->actionConfig]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1]);

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS, $this->actionHandler->getTags())
            ->andReturn(10)
            ->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL, $this->actionHandler->getTags())
            ->andReturn(0)
            ->once();
        $this->randomService->shouldReceive('isSuccessful')->with(10)->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->with(0)->andReturn(false)->once();
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->once();

        $scrap = new GameItem(new Place());

        $this->eventService->shouldReceive('callEvent')->once();

        // Success
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(0, $player->getStatuses());
    }
}
