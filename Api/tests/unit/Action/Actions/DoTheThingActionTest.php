<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;

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

    /* @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface|Mockery\Mock $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::DO_THE_THING);

        $this->diseaseCauseService = \Mockery::mock(DiseaseCauseServiceInterface::class);
        $this->playerDiseaseService = \Mockery::mock(PlayerDiseaseServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = \Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new DoTheThing(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->diseaseCauseService,
            $this->playerDiseaseService,
            $this->randomService,
            $this->roomLogService,
            $this->statusService
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
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->twice();
        $this->statusService->shouldReceive('createStatusFromName')->twice();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
