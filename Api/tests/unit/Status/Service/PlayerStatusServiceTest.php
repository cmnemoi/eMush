<?php

namespace Mush\Tests\unit\Status\Service;

use Codeception\PHPUnit\TestCase;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\PlayerStatusService;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class PlayerStatusServiceTest extends TestCase
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    private PlayerStatusServiceInterface $playerStatusService;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->playerStatusService = new PlayerStatusService($this->statusService);
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

        $this->statusService->shouldReceive('removeStatus')->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player suicidal, improvement of mental
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(10);

        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setStatusName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->statusService->shouldReceive('removeStatus')->once();
        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
    }

    public function testHandleMoralDemoralized()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(3);

        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player Already demoralized
        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setStatusName(PlayerStatusEnum::DEMORALIZED);
        $demoralizedStatus = new Status($player, $demoralizedConfig);

        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->statusService->shouldReceive('removeStatus')->never();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        self::assertNotEmpty($player->getStatuses());

        // Player Already suicidal, improvement of mental
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(3);

        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setStatusName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->statusService->shouldReceive('removeStatus')->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
    }

    public function testHandleMoralSuicidal()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(1);

        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());

        // Player Already suicidal
        $suicidalConfig = new StatusConfig();
        $suicidalConfig->setStatusName(PlayerStatusEnum::SUICIDAL);
        $suicidalStatus = new Status($player, $suicidalConfig);

        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->statusService->shouldReceive('removeStatus')->never();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
        self::assertCount(1, $player->getStatuses());

        // Player was demoralized
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setMoralPoint(1);
        $demoralizedConfig = new StatusConfig();
        $demoralizedConfig->setStatusName(PlayerStatusEnum::DEMORALIZED);
        $demoralizedStatus = new Status($player, $demoralizedConfig);

        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->statusService->shouldReceive('removeStatus')->once();

        $this->playerStatusService->handleMoralStatus($player, new \DateTime());
    }

    public function testHandleHumanSatietyNoStatus()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(0);

        $starvingConfig = new StatusConfig();
        $starvingConfig->setStatusName(PlayerStatusEnum::STARVING);
        new Status($player, $starvingConfig);

        $starvingConfig = new StatusConfig();
        $starvingConfig->setStatusName(PlayerStatusEnum::STARVING);
        $starvingStatus = new Status($player, $starvingConfig);

        $this->statusService->shouldReceive('removeStatus')->twice();
        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());

        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(0);
        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setStatusName(PlayerStatusEnum::FULL_STOMACH);
        $fullBellyStatus = new Status($player, $fullStomachConfig);

        $this->statusService->shouldReceive('removeStatus')->once();
        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleNegativeSatiety()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player
            ->setSatiety(-40)
            ->setPlace(new Place());

        $this->statusService->shouldReceive('removeStatus')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleNegativeSatietyWhenAlreadyStarved()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(-40);

        $starvingWarningConfig = new StatusConfig();
        $starvingWarningConfig->setStatusName(PlayerStatusEnum::STARVING_WARNING);
        new Status($player, $starvingWarningConfig);

        $starvingConfig = new StatusConfig();
        $starvingConfig->setStatusName(PlayerStatusEnum::STARVING);
        new Status($player, $starvingConfig);

        $this->statusService->shouldReceive('removeStatus')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleStarvingStatusWhenFullStomach()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player
            ->setSatiety(-40)
            ->setPlace(new Place());
        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setStatusName(PlayerStatusEnum::FULL_STOMACH);
        new Status($player, $fullStomachConfig);

        $this->statusService->shouldReceive('removeStatus')->twice();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleFullStomachStatus()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(40);

        $this->statusService->shouldReceive('removeStatus')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleFullStomachWhenStarving()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);

        $starvingWarningConfig = new StatusConfig();
        $starvingWarningConfig->setStatusName(PlayerStatusEnum::STARVING_WARNING);
        new Status($player, $starvingWarningConfig);

        $player->setSatiety(40);
        $starvingConfig = new StatusConfig();
        $starvingConfig->setStatusName(PlayerStatusEnum::STARVING);
        new Status($player, $starvingConfig);

        $this->statusService->shouldReceive('removeStatus')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleFullStomachStatusWhenAlreadyFull()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(40);

        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setStatusName(PlayerStatusEnum::FULL_STOMACH);
        new Status($player, $fullStomachConfig);

        $this->statusService->shouldReceive('removeStatus')->once();
        $this->statusService->shouldReceive('createStatusFromName')->never();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
    }

    public function testHandleSatietyStatusMush()
    {
        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(4);
        $mushConfig = new StatusConfig();
        $mushConfig->setStatusName(PlayerStatusEnum::MUSH);
        $mushStatus = new Status($player, $mushConfig);

        $this->statusService->shouldReceive('removeStatus')->times(4);
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        self::assertCount(1, $player->getStatuses());

        $player = $this->createPlayer(0, 0, 0, 0, 0);
        $player->setSatiety(-26);
        $mushStatus = new Status($player, $mushConfig);

        $this->playerStatusService->handleSatietyStatus($player, new \DateTime());
        self::assertCount(1, $player->getStatuses());
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
            ->setInitHealthPoint($health);

        $player = new Player();
        $player
            ->setPlayerVariables($characterConfig)
            ->setDaedalus(new Daedalus());

        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );

        $player->setPlayerInfo($playerInfo);

        return $player;
    }
}
