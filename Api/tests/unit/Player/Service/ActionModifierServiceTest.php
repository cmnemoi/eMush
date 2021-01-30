<?php

namespace Mush\Test\Player\Service;

use Mockery;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class ActionModifierServiceTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    private ActionModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->service = new ActionModifierService(
            $this->statusService,
            $this->roomLogService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testSatietyModifier()
    {
        $player = new Player();
        $modifier = new Modifier();
        $modifier->setTarget(ModifierTargetEnum::SATIETY);
        $modifier->setDelta(-1);

        $this->service->handlePlayerModifier($player, $modifier);

        $this->statusService->shouldReceive('createCoreStatus')->once();

        $modifier->setDelta(4);

        $this->service->handlePlayerModifier($player, $modifier);

        $status = new Status($player);
        $status->setName(PlayerStatusEnum::FULL_STOMACH);

        $modifier->setDelta(-1);

        $this->service->handlePlayerModifier($player, $modifier);

        $this->assertEquals(3, $player->getSatiety());
        $this->assertCount(1, $player->getStatuses());

        $this->service->handlePlayerModifier($player, $modifier);

        $this->assertEquals(2, $player->getSatiety());
        $this->assertCount(0, $player->getStatuses());
    }
}
