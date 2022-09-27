<?php

namespace unit\Status\Service;

use Codeception\PHPUnit\TestCase;
use Mockery;
use Mush\Event\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\PlayerStatusService;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class PlayerStatusServiceTest extends TestCase
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    private PlayerStatusServiceInterface $playerStatusService;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventServiceInterface::class);

        $this->playerStatusService = new PlayerStatusService($this->statusService, $this->eventDispatcher);
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

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player demoralized, improvement of mental
        $player = new Player();
        $player->setMoralPoint(10);

        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setName(PlayerStatusEnum::DEMORALIZED);
        $demoralizedStatus = new Status($player, $demoralizedConfig);

        $this->statusService->shouldReceive('delete')->with($demoralizedStatus)->once();
        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player suicidal, improvement of mental
        $player = new Player();
        $player->setMoralPoint(10);

        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->statusService->shouldReceive('delete')->with($suicidalStatus)->once();
        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
    }

    public function testHandleMoralDemoralized()
    {
        $player = new Player();
        $player->setMoralPoint(3);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::DEMORALIZED && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player Already demoralized
        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setName(PlayerStatusEnum::DEMORALIZED);
        $demoralizedStatus = new Status($player, $demoralizedConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        $this->assertNotEmpty($player->getStatuses());

        // Player Already suicidal, improvement of mental
        $player = new Player();
        $player->setMoralPoint(3);

        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::DEMORALIZED && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->statusService->shouldReceive('delete')->with($suicidalStatus)->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
    }

    public function testHandleMoralSuicidal()
    {
        $player = new Player();
        $player->setMoralPoint(1);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::SUICIDAL && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player Already suicidal
        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        // Player was demoralized
        $player = new Player();
        $player->setMoralPoint(1);
        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setName(PlayerStatusEnum::DEMORALIZED);
        $demoralizedStatus = new Status($player, $demoralizedConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::SUICIDAL && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->statusService->shouldReceive('delete')->with($demoralizedStatus)->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
    }

    public function testHandleHumanSatietyNoStatus()
    {
        $player = new Player();
        $player->setSatiety(0);

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(0);
        $starvingConfig = new StatusConfig();
        $starvingConfig->setName(PlayerStatusEnum::STARVING);
        $starvingStatus = new Status($player, $starvingConfig);

        $this->statusService->shouldReceive('delete')->with($starvingStatus);
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(0);
        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setName(PlayerStatusEnum::FULL_STOMACH);
        $fullBellyStatus = new Status($player, $fullStomachConfig);

        $this->statusService->shouldReceive('delete')->with($fullBellyStatus);
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleStarvingStatus()
    {
        $player = new Player();
        $player
            ->setSatiety(-40)
            ->setPlace(new Place())
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::STARVING && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(-40);
        $starvingConfig = new StatusConfig();
        $starvingConfig->setName(PlayerStatusEnum::STARVING);
        $starvingStatus = new Status($player, $starvingConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player
            ->setSatiety(-40)
            ->setPlace(new Place())
        ;
        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setName(PlayerStatusEnum::FULL_STOMACH);
        $fullBellyStatus = new Status($player, $fullStomachConfig);

        $this->statusService
            ->shouldReceive('delete')
            ->with($fullBellyStatus)
            ->once()
        ;
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::STARVING && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleFullStomachStatus()
    {
        $player = new Player();
        $player->setSatiety(40);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(40);
        $starvingConfig = new StatusConfig();
        $starvingConfig->setName(PlayerStatusEnum::STARVING);
        $starvingStatus = new Status($player, $starvingConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->statusService->shouldReceive('delete')->with($starvingStatus)->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(40);

        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setName(PlayerStatusEnum::FULL_STOMACH);
        $fullBellyStatus = new Status($player, $fullStomachConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());
    }

    public function testHandleSatietyStatusMush()
    {
        $player = new Player();
        $player->setSatiety(4);
        $mushConfig = new StatusConfig();
        $mushConfig->setName(PlayerStatusEnum::MUSH);
        $mushStatus = new Status($player, $mushConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        $player = new Player();
        $player->setSatiety(-26);
        $mushStatus = new Status($player, $mushConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());
    }
}
