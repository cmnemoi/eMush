<?php

namespace unit\Status\Service;

use Codeception\PHPUnit\TestCase;
use Mockery;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\PlayerStatusService;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class PlayerStatusServiceTest extends TestCase
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;

    private PlayerStatusServiceInterface $playerStatusService;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);

        $this->playerStatusService = new PlayerStatusService($this->statusService, $this->roomLogService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testHandleMoralNoStatuses()
    {
        $player = new Player();
        $player->setMoralPoint(10);

        $this->playerStatusService->handleMoralStatus($player);

        //Player demoralized, improvement of mental
        $player = new Player();
        $player->setMoralPoint(10);

        $demoralizedStatus = new Status($player);
        $demoralizedStatus->setName(PlayerStatusEnum::DEMORALIZED);

        $this->playerStatusService->handleMoralStatus($player);
        $this->assertEmpty($player->getStatuses());

        //Player suicidal, improvement of mental
        $player = new Player();
        $player->setMoralPoint(10);

        $suicidalStatus = new Status($player);
        $suicidalStatus->setName(PlayerStatusEnum::SUICIDAL);

        $this->playerStatusService->handleMoralStatus($player);
        $this->assertEmpty($player->getStatuses());
    }

    public function testHandleMoralDemoralized()
    {
        $player = new Player();
        $player->setMoralPoint(3);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::DEMORALIZED))
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player);

        //Player Already demoralized
        $demoralizedStatus = new Status($player);
        $demoralizedStatus->setName(PlayerStatusEnum::DEMORALIZED);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::DEMORALIZED))
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player);
        $this->assertNotEmpty($player->getStatuses());

        //Player Already suicidal, improvement of mental
        $player = new Player();
        $player->setMoralPoint(3);

        $suicidalStatus = new Status($player);
        $suicidalStatus->setName(PlayerStatusEnum::SUICIDAL);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::DEMORALIZED))
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player);
        $this->assertEmpty($player->getStatuses());
    }

    public function testHandleMoralSuicidal()
    {
        $player = new Player();
        $player->setMoralPoint(1);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::SUICIDAL))
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player);

        //Player Already suicidal
        $demoralizedStatus = new Status($player);
        $demoralizedStatus->setName(PlayerStatusEnum::SUICIDAL);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::SUICIDAL))
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player);
        $this->assertCount(1, $player->getStatuses());

        //Player was demoralized
        $player = new Player();
        $player->setMoralPoint(1);
        $suicidalStatus = new Status($player);
        $suicidalStatus->setName(PlayerStatusEnum::DEMORALIZED);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::SUICIDAL))
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player);
        $this->assertEmpty($player->getStatuses());
    }

    public function testHandleHumanSatietyNoStatus()
    {
        $player = new Player();
        $player->setSatiety(0);

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(0);
        $starvingStatus = new Status($player);
        $starvingStatus->setName(PlayerStatusEnum::STARVING);

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertEmpty($player->getStatuses());

        $player = new Player();
        $player->setSatiety(0);
        $fullBellyStatus = new Status($player);
        $fullBellyStatus->setName(PlayerStatusEnum::FULL_STOMACH);

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertEmpty($player->getStatuses());
    }

    public function testHandleStarvingStatus()
    {
        $player = new Player();
        $player
            ->setSatiety(-40)
            ->setPlace(new Place())
        ;

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::STARVING))
            ->once()
        ;
        $this->roomLogService->shouldReceive('createLog')->once();
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(-40);
        $starvingStatus = new Status($player);
        $starvingStatus->setName(PlayerStatusEnum::STARVING);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::STARVING))
            ->never()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        $player = new Player();
        $player
            ->setSatiety(-40)
            ->setPlace(new Place())
        ;
        $fullBellyStatus = new Status($player);
        $fullBellyStatus->setName(PlayerStatusEnum::FULL_STOMACH);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::STARVING))
            ->once()
        ;
        $this->roomLogService->shouldReceive('createLog')->once();
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertEmpty($player->getStatuses());
    }

    public function testHandleFullStomachStatus()
    {
        $player = new Player();
        $player->setSatiety(40);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::FULL_STOMACH))
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(40);
        $starvingStatus = new Status($player);
        $starvingStatus->setName(PlayerStatusEnum::STARVING);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::FULL_STOMACH))
            ->once()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertEmpty($player->getStatuses());

        $player = new Player();
        $player->setSatiety(40);
        $fullBellyStatus = new Status($player);
        $fullBellyStatus->setName(PlayerStatusEnum::FULL_STOMACH);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::FULL_STOMACH))
            ->never()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());
    }

    public function testHandleSatietyStatusMush()
    {
        $player = new Player();
        $player->setSatiety(4);
        $mushStatus = new Status($player);
        $mushStatus->setName(PlayerStatusEnum::MUSH);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->withArgs(fn (string $name) => ($name === PlayerStatusEnum::FULL_STOMACH))
            ->once()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        $player = new Player();
        $player->setSatiety(-26);
        $mushStatus = new Status($player);
        $mushStatus->setName(PlayerStatusEnum::MUSH);

        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->never()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());
    }
}
