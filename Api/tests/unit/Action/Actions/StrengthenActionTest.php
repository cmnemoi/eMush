<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\StrengthenHull;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Config\ChargeStatusConfig;

/**
 * @internal
 */
final class StrengthenActionTest extends AbstractActionTest
{
    /** @var Mockery\Mock|RandomServiceInterface */

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::REPAIR, 1);
        $this->actionConfig->setOutputQuantity(5);

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->actionHandler = new StrengthenHull(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();

        $gameItem
            ->setEquipment($item)
            ->setName('item');

        $player = $this->createPlayer($daedalus, $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS, $this->actionHandler->getTags())
            ->andReturn(10)
            ->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        // Fail try
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Fail::class, $result);
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();

        $gameItem
            ->setName('item')
            ->setEquipment($item);

        $player = $this->createPlayer($daedalus, $room);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName('attempt');
        $attempt = new Attempt($player, $attemptConfig);
        $attempt
            ->setAction($this->actionConfig->getActionName()->value);
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS, $this->actionHandler->getTags())
            ->andReturn(10)
            ->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL, $this->actionHandler->getTags())
            ->andReturn(10)
            ->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::OUTPUT_QUANTITY, $this->actionHandler->getTags())
            ->andReturn(5)
            ->once();

        $this->eventService->shouldReceive('callEvent')->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Success
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
