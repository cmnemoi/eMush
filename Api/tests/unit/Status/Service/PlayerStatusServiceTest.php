<?php

namespace unit\Status\Service;

use Codeception\PHPUnit\TestCase;
use Mockery;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\PlayerStatusService;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlayerStatusServiceTest extends TestCase
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private PlayerStatusServiceInterface $playerStatusService;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

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

        //Player demoralized, improvement of mental
        $player = new Player();
        $player->setMoralPoint(10);

        $demoralizedStatus = new Status($player);
        $demoralizedStatus->setName(PlayerStatusEnum::DEMORALIZED);

        $this->statusService->shouldReceive('delete')->with($demoralizedStatus)->once();
        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        //Player suicidal, improvement of mental
        $player = new Player();
        $player->setMoralPoint(10);

        $suicidalStatus = new Status($player);
        $suicidalStatus->setName(PlayerStatusEnum::SUICIDAL);

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

        //Player Already demoralized
        $demoralizedStatus = new Status($player);
        $demoralizedStatus->setName(PlayerStatusEnum::DEMORALIZED);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        $this->assertNotEmpty($player->getStatuses());

        //Player Already suicidal, improvement of mental
        $player = new Player();
        $player->setMoralPoint(3);

        $suicidalStatus = new Status($player);
        $suicidalStatus->setName(PlayerStatusEnum::SUICIDAL);

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

        //Player Already suicidal
        $demoralizedStatus = new Status($player);
        $demoralizedStatus->setName(PlayerStatusEnum::SUICIDAL);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        //Player was demoralized
        $player = new Player();
        $player->setMoralPoint(1);
        $suicidalStatus = new Status($player);
        $suicidalStatus->setName(PlayerStatusEnum::DEMORALIZED);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::SUICIDAL && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->statusService->shouldReceive('delete')->with($suicidalStatus)->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
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

        $this->statusService->shouldReceive('delete')->with($starvingStatus);
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(0);
        $fullBellyStatus = new Status($player);
        $fullBellyStatus->setName(PlayerStatusEnum::FULL_STOMACH);

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
        $starvingStatus = new Status($player);
        $starvingStatus->setName(PlayerStatusEnum::STARVING);

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
        $fullBellyStatus = new Status($player);
        $fullBellyStatus->setName(PlayerStatusEnum::FULL_STOMACH);

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
        $starvingStatus = new Status($player);
        $starvingStatus->setName(PlayerStatusEnum::STARVING);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH && $event->getStatusHolder() === $player)
            ->once()
        ;
        $this->statusService->shouldReceive('delete')->with($starvingStatus)->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = new Player();
        $player->setSatiety(40);
        $fullBellyStatus = new Status($player);
        $fullBellyStatus->setName(PlayerStatusEnum::FULL_STOMACH);

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
        $mushStatus = new Status($player);
        $mushStatus->setName(PlayerStatusEnum::MUSH);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        $player = new Player();
        $player->setSatiety(-26);
        $mushStatus = new Status($player);
        $mushStatus->setName(PlayerStatusEnum::MUSH);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());
    }
}
