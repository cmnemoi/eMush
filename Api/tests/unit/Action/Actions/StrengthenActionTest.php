<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\StrengthenHull;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;

class StrengthenActionTest extends AbstractActionTest
{
    /** @var RandomServiceInterface|Mockery\Mock */
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private ActionModifierServiceInterface $actionModifierService;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::REPAIR, 1);

        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->action = new StrengthenHull(
            $this->eventDispatcher,
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
        Mockery::close();
    }

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();

        $gameItem
            ->setEquipment($item)
            ->setHolder($room)
        ;

        $player = $this->createPlayer($daedalus, $room);

        $attempt = new Attempt($player);
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction($this->action->getActionName())
        ;
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        //Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();

        $gameItem
            ->setEquipment($item)
            ->setHolder($room)
        ;

        $player = $this->createPlayer($daedalus, $room);

        $attempt = new Attempt($player);
        $attempt
            ->setName(StatusEnum::ATTEMPT)
            ->setAction($this->action->getActionName())
        ;
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        //Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
