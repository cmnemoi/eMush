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

class SabotageActionTest extends AbstractActionTest
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::SABOTAGE, 2);

        $this->action = new Sabotage(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
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
            ->setName('item')
        ;

        $player = $this->createPlayer(new Daedalus(), $room);

        $room->setDaedalus($player->getDaedalus());

        $mushStatus = new ChargeStatus($player, new ChargeStatusConfig());

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $attempt = new Attempt(new Player(), new ChargeStatusConfig());
        $attempt
            ->setAction($this->action->getActionName())
        ;
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(10)
            ->once()
        ;
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        // Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals(0, $attempt->getCharge());

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(100)
            ->once()
        ;
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $gameItem, ActionVariableEnum::PERCENTAGE_CRITICAL)
            ->andReturn(100)
            ->once()
        ;
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->eventService->shouldReceive('callEvent');

        // Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(1, $player->getStatuses());
    }
}
