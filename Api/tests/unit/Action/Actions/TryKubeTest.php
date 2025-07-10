<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\TryKube;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class TryKubeTest extends AbstractActionTest
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var Mockery\Mock|StatusServiceInterface */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::TRY_KUBE, 1);

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->actionHandler = new TryKube(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->statusService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testUnsuccessful()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item)->setName('cube');

        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false);

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }

    public function testSuccessful()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $player = $this->createPlayer($daedalus, $room);

        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem->setEquipment($item)->setName('cube');

        $item->setActionConfigs(new ArrayCollection([$this->actionConfig]));

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true);
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
