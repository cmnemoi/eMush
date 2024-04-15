<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\SelfHeal;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;

/**
 * @internal
 */
final class SelfHealTest extends AbstractActionTest
{
    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SELF_HEAL);
        $this->actionEntity->setOutputQuantity(3);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->action = new SelfHeal(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService,
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

        $this->playerService->shouldReceive('persist');
        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->action->loadParameters($this->actionEntity, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, null, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(3)
            ->once();
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
