<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\StrengthenHull;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;

class StrengthenActionTest extends AbstractActionTest
{
    /** @var RandomServiceInterface|Mockery\Mock */

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

        $this->action = new StrengthenHull(
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
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();

        $gameItem
            ->setEquipment($item)
            ->setName('item')
        ;

        $player = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(10)
            ->once()
        ;
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        // Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();

        $gameItem
            ->setName('item')
            ->setEquipment($item)
        ;

        $player = $this->createPlayer($daedalus, $room);

        $attempt = new Attempt($player, new ChargeStatusConfig());
        $attempt
            ->setAction($this->action->getActionName())
        ;
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(10)
            ->once()
        ;
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL)
            ->andReturn(10)
            ->once()
        ;
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $this->eventService->shouldReceive('callEvent')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
