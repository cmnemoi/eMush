<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class DoTheThingActionTest extends AbstractActionTest
{
    private StatusServiceInterface $statusService;
    private PlayerVariableServiceInterface $playerVariableService;
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::DO_THE_THING);

        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->playerVariableService = Mockery::mock(PlayerVariableServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->action = new DoTheThing(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->playerVariableService,
            $this->randomService,
            $this->statusService,
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
        $daedalus = new Daedalus();
        $room = new Place();

        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->times(4);
        $this->playerVariableService->shouldReceive('getMaxPlayerVariable')->andReturn(14)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
