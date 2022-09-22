<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\DoTheThing;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Repository\DiseaseCausesConfigRepository;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class DoTheThingActionTest extends AbstractActionTest
{
    private DiseaseCauseConfig $diseaseCauseConfig;
    private DiseaseCausesConfigRepository $diseaseCausesConfigRepository;
    private StatusServiceInterface $statusService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PlayerVariableServiceInterface $playerVariableService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::DO_THE_THING);

        $this->diseaseCausesConfigRepository = Mockery::mock(DiseaseCausesConfigRepository::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->playerDiseaseService = Mockery::mock(PlayerDiseaseServiceInterface::class);
        $this->playerVariableService = Mockery::mock(PlayerVariableServiceInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->diseaseCauseConfig = Mockery::mock(DiseaseCauseConfig::class);

        $this->action = new DoTheThing(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->diseaseCausesConfigRepository,
            $this->playerDiseaseService,
            $this->playerVariableService,
            $this->randomService,
            $this->roomLogService,
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
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->twice();
        $this->diseaseCauseConfig->shouldReceive('getDiseasesRate')->andReturn(0)->once();
        $this->diseaseCausesConfigRepository
            ->shouldReceive('findBy')
            ->with(['causeName' => 'sex'])
            ->andReturn([0 => $this->diseaseCauseConfig])
            ->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
