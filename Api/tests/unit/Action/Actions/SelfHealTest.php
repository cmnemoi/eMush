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
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::SELF_HEAL);
        $this->actionConfig->setOutputQuantity(3);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->actionHandler = new SelfHeal(
            $this->eventService,
            $this->actionService,
            $this->validator,
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

        $this->playerService->shouldReceive('persist');
        $this->eventService->shouldReceive('callEvent');

        $player = $this->createPlayer(new Daedalus(), $room);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, null, ActionVariableEnum::OUTPUT_QUANTITY, $this->actionHandler->getTags())
            ->andReturn(3)
            ->once();
        $this->eventService->shouldReceive('callEvent');
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
