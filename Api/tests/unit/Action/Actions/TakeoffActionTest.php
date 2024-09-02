<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Takeoff;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Repository\PlayerNotificationRepositoryInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\RoomLog\Service\RoomLogServiceInterface;

/**
 * @internal
 */
final class TakeoffActionTest extends AbstractActionTest
{
    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::TAKEOFF, 2, 0);
        $this->actionConfig->setCriticalRate(20);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->actionHandler = new Takeoff(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->createStub(PatrolShipManoeuvreServiceInterface::class),
            $this->playerService,
            $this->randomService,
            $this->createStub(RoomLogServiceInterface::class),
            new UpdatePlayerNotificationService(
                $this->createStub(PlayerNotificationRepositoryInterface::class),
            )
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

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(false);
        $this->playerService->shouldReceive('changePlace')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertNotInstanceOf(CriticalSuccess::class, $result);
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

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $patroller);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')->andReturn(100);
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(true);
        $this->playerService->shouldReceive('changePlace')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(CriticalSuccess::class, $result);
    }
}
