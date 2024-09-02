<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Sabotage;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Service\StatusServiceInterface;

/**
 * @internal
 */
final class SabotageActionTest extends AbstractActionTest
{
    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private Mockery\Mock|StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->createActionEntity(ActionEnum::SABOTAGE, 2);

        $this->actionHandler = new Sabotage(
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
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $item->setIsBreakable(true);
        $gameItem
            ->setEquipment($item)
            ->setName('item');

        $player = $this->createPlayer(new Daedalus(), $room);

        $room->setDaedalus($player->getDaedalus());

        $mushConfig = new ChargeStatusConfig();
        $mushConfig->setStatusName('mush');
        $mushStatus = new ChargeStatus($player, $mushConfig);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $attemptConfig = new ChargeStatusConfig();
        $attemptConfig->setStatusName('attempt');
        $attempt = new Attempt(new Player(), $attemptConfig);
        $attempt
            ->setAction($this->actionConfig->getActionName()->value);
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

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
        self::assertCount(0, $room->getEquipments()->first()->getStatuses());
        self::assertCount(1, $player->getStatuses());
        self::assertSame(0, $attempt->getCharge());

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS, $this->actionHandler->getTags())
            ->andReturn(100)
            ->once();
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL, $this->actionHandler->getTags())
            ->andReturn(100)
            ->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->eventService->shouldReceive('callEvent');

        // Success
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
        self::assertCount(1, $room->getEquipments());
        self::assertCount(0, $room->getEquipments()->first()->getStatuses());
        self::assertCount(1, $player->getStatuses());
    }
}
