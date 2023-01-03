<?php

namespace unit\Status\Service;

use Codeception\PHPUnit\TestCase;
use Mockery;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\PlayerStatusService;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerStatusServiceTest extends TestCase
{
    private StatusServiceInterface|Mockery\Mock $statusService;

    private Mockery\Mock|EventDispatcherInterface $eventDispatcher;

    private PlayerStatusServiceInterface $playerStatusService;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $this->playerStatusService = new PlayerStatusService($this->statusService, $this->eventDispatcher);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testHandleMoralNoStatuses()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(10);

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player demoralized, improvement of mental
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(10);

        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setStatusName(PlayerStatusEnum::DEMORALIZED);
        $demoralizedStatus = new Status($player, $demoralizedConfig);

        $this->statusService->shouldReceive('delete')->with($demoralizedStatus)->once();
        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player suicidal, improvement of mental
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(10);

        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setStatusName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->statusService->shouldReceive('delete')->with($suicidalStatus)->once();
        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
    }

    public function testHandleMoralDemoralized()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(3);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::DEMORALIZED && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player Already demoralized
        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setStatusName(PlayerStatusEnum::DEMORALIZED);
        $demoralizedStatus = new Status($player, $demoralizedConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        $this->assertNotEmpty($player->getStatuses());

        // Player Already suicidal, improvement of mental
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(3);

        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setStatusName(PlayerStatusEnum::SUICIDAL);
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
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(1);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::SUICIDAL && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player Already suicidal
        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setStatusName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        // Player was demoralized
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(1);
        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setStatusName(PlayerStatusEnum::DEMORALIZED);
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
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(0);
        $starvingConfig = new StatusConfig();
        $starvingConfig->setStatusName(PlayerStatusEnum::STARVING);
        $starvingStatus = new Status($player, $starvingConfig);

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->statusService->shouldReceive('createStatusFromName')->withSomeOfArgs($starvingStatus);
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(0);
        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setStatusName(PlayerStatusEnum::FULL_STOMACH);
        $fullBellyStatus = new Status($player, $fullStomachConfig);

        $this->eventDispatcher->shouldReceive('dispatch')->once();
        $this->statusService->shouldReceive('delete')->with($fullBellyStatus);
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleNegativeSatiety()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player
            ->setSatiety(-40)
            ->setPlace(new Place())
        ;

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::STARVING &&
                $event->getStatusHolder() === $player
            )
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleNegativeSatietyWhenAlreadyStarved()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(-40);

        $starvingConfig = new StatusConfig();
        $starvingConfig->setStatusName(PlayerStatusEnum::STARVING);
        new Status($player, $starvingConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleStarvingStatusWhenFullStomach()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player
            ->setSatiety(-40)
            ->setPlace(new Place())
        ;
        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setStatusName(PlayerStatusEnum::FULL_STOMACH);
        new Status($player, $fullStomachConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH &&
                $event->getStatusHolder() === $player
            )
            ->once()
        ;
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::STARVING &&
                $event->getStatusHolder() === $player
            )
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleFullStomachStatus()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(40);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH &&
                $event->getStatusHolder() === $player
            )
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleFullStomachWhenStarving()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(40);
        $starvingConfig = new StatusConfig();
        $starvingConfig->setStatusName(PlayerStatusEnum::STARVING);
        new Status($player, $starvingConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH &&
                $event->getStatusHolder() === $player
            )
            ->once()
        ;
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::STARVING &&
                $event->getStatusHolder() === $player
            )
            ->once()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleFullStomachStatusWhenAlreadyFull()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(40);

        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setStatusName(PlayerStatusEnum::FULL_STOMACH);
        new Status($player, $fullStomachConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleSatietyStatusMush()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(4);
        $mushConfig = new StatusConfig();
        $mushConfig->setStatusName(PlayerStatusEnum::MUSH);
        $mushStatus = new Status($player, $mushConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === PlayerStatusEnum::FULL_STOMACH && $event->getStatusHolder() === $player)
            ->once()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());

        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(-26);
        $mushStatus = new Status($player, $mushConfig);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->never()
        ;

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        $this->assertCount(1, $player->getStatuses());
    }

    protected function createPlayer(int $health, int $moral, int $movement, int $action, int $satiety): Player
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setMaxHealthPoint(16)
            ->setMaxMoralPoint(16)
            ->setMaxActionPoint(16)
            ->setMaxMovementPoint(16)
            ->setInitActionPoint($action)
            ->setInitMovementPoint($movement)
            ->setInitMoralPoint($moral)
            ->setInitSatiety($satiety)
            ->setInitHealthPoint($health)
        ;

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
        ;

        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );

        $player->setPlayerInfo($playerInfo);

        return $player;
    }
}
