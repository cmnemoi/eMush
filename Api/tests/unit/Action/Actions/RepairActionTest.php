<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\Actions\Repair;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;

use Mush\Action\ActionResult\{
    Fail,
    Success
};
use Mush\Equipment\Entity\{
    Config\ItemConfig,
    GameItem
};

class RepairActionTest extends AbstractActionTest
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REPAIR, 1);

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->action = new Repair(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
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
        $item
            ->setIsBreakable(true)
        ;

        $gameItem
            ->setEquipment($item)
        ;

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        // Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $item
            ->setIsBreakable(true)
        ;

        $gameItem
            ->setEquipment($item)
        ;

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->eventService->shouldReceive('callEvent')->once();

        // Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
    }
}
