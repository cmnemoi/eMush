<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Takeoff;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;

class TakeoffActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var PlaceServiceInterface|Mockery\Mock */
    private PlaceServiceInterface $placeService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::TAKEOFF, 2, 0);

        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->placeService = \Mockery::mock(PlaceServiceInterface::class);

        $this->action = new Takeoff(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService,
            $this->playerService,
            $this->placeService,
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
        $roomStart = new Place();
        $roomEnd = new Place();
        $patroller = new GameEquipment($roomStart);
        $patroller->setName(EquipmentEnum::PATROL_SHIP);

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $player = $this->createPlayer(new Daedalus(), $roomStart);

        $this->action->loadParameters($this->actionEntity, $player, $patroller);

        $this->placeService->shouldReceive('findByNameAndDaedalus')->andReturn($roomEnd);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventService->shouldReceive('callEvent')->times(3);
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals($player->getPlace(), $roomEnd);
    }
}
