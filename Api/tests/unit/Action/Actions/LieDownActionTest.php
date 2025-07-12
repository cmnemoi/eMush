<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\LieDown;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class LieDownActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::LIE_DOWN);

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new LieDown(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = new GameEquipment($room);
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionConfig]));
        $item = new EquipmentConfig();
        $item
            ->setEquipmentName(EquipmentEnum::BED)
            ->setMechanics(new ArrayCollection([$tool]));

        $gameEquipment
            ->setEquipment($item)
            ->setName(EquipmentEnum::BED);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameEquipment);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertCount(0, $player->getStatuses());
        self::assertCount(0, $gameEquipment->getTargetingStatuses());
        self::assertSame(10, $player->getActionPoint());
    }
}
