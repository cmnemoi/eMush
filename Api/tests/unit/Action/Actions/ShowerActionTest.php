<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class ShowerActionTest extends AbstractActionTest
{
    private Mockery\Mock|StatusServiceInterface $statusService;
    private Mockery\Mock|RandomServiceInterface $randomService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::TAKE_SHOWER, 2);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->actionHandler = new Shower(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService,
            $this->randomService
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
        $room = new Place();

        $gameItem = new GameEquipment($room);
        $item = new EquipmentConfig();
        $gameItem
            ->setEquipment($item)
            ->setName('item');

        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('removeStatus');

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
