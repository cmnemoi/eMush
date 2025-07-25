<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class RepairActionTest extends AbstractActionTest
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::REPAIR, 1);

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new Repair(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->statusService,
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
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $item
            ->setBreakableType(BreakableTypeEnum::BREAKABLE);

        $gameItem
            ->setEquipment($item)
            ->setName('item');

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $room->setDaedalus($player->getDaedalus());

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS, $this->actionHandler->getTags())
            ->andReturn(10)
            ->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        // Fail try
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $item
            ->setBreakableType(BreakableTypeEnum::BREAKABLE);

        $gameItem
            ->setEquipment($item)
            ->setName('item');

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $room->setDaedalus($player->getDaedalus());

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS, $this->actionHandler->getTags())
            ->andReturn(100)
            ->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL, $this->actionHandler->getTags())
            ->andReturn(0)
            ->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $this->statusService->shouldReceive('removeStatus')->once();

        // Success
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
    }
}
