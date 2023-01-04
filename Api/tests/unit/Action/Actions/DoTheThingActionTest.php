<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class DoTheThingActionTest extends AbstractActionTest
{
    /* @var DiseaseCauseServiceInterface|Mockery\Mock */
    private DiseaseCauseServiceInterface|Mockery\Mock $diseaseCauseService;
    /* @var PlayerDiseaseServiceInterface|Mockery\Mock */
    private PlayerDiseaseServiceInterface|Mockery\Mock $playerDiseaseService;
    /* @var PlayerVariableServiceInterface|Mockery\Mock */
    private PlayerVariableServiceInterface|Mockery\Mock $playerVariableService;
    /* @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface|Mockery\Mock $randomService;
    /* @var RoomLogServiceInterface|Mockery\Mock */
    private RoomLogServiceInterface|Mockery\Mock $roomLogService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::DO_THE_THING);

        $this->diseaseCauseService = \Mockery::mock(DiseaseCauseServiceInterface::class);
        $this->playerDiseaseService = \Mockery::mock(PlayerDiseaseServiceInterface::class);
        $this->playerVariableService = \Mockery::mock(PlayerVariableServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = \Mockery::mock(RoomLogServiceInterface::class);

        $this->action = new DoTheThing(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->diseaseCauseService,
            $this->playerDiseaseService,
            $this->playerVariableService,
            $this->randomService,
            $this->roomLogService,
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

        $player = $this->createPlayer($daedalus, $room);

        $targetPlayer = $this->createPlayer($daedalus, $room);

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch')->times(4);
        $this->playerVariableService->shouldReceive('getMaxPlayerVariable')->andReturn(14)->times(2);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
