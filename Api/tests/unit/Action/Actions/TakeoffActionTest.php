<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Takeoff;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Service\PlayerServiceInterface;

class TakeoffActionTest extends AbstractActionTest
{
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TAKEOFF, 2, 0);
        $this->actionEntity->setCriticalRate(20);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->action = new Takeoff(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService,
            $this->randomService
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

        $roomStart = new Place();
        $roomStart->setName(RoomEnum::ALPHA_BAY);
        $roomStart->setDaedalus($daedalus);
        $roomEnd = new Place();
        $roomEnd->setName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN);
        $roomEnd->setDaedalus($daedalus);
        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(false);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertNotInstanceOf(CriticalSuccess::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();

        $roomStart = new Place();
        $roomStart->setName(RoomEnum::ALPHA_BAY);
        $roomStart->setDaedalus($daedalus);
        $roomEnd = new Place();
        $roomEnd->setName(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN);
        $roomEnd->setDaedalus($daedalus);
        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN);

        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer($daedalus, $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(true);
        $this->eventService->shouldReceive('callEvent')->times(1);

        $result = $this->action->execute();

        $this->assertInstanceOf(CriticalSuccess::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);
    }
}
