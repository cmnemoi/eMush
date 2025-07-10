<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;

/**
 * @internal
 */
final class MoveActionTest extends AbstractActionTest
{
    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::MOVE, 0, 1);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->actionHandler = new Move(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->playerService,
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
        $roomStart = new Place();
        $roomEnd = new Place();
        $door = new Door($roomStart);
        $door
            ->addRoom($roomStart)
            ->addRoom($roomEnd);
        $roomStart->addDoor($door);
        $roomEnd->addDoor($door);

        $player = $this->createPlayer(new Daedalus(), $roomStart);

        $this->playerService->shouldReceive('changePlace')->twice()->with($player, $roomEnd);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $door);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $this->eventService->shouldReceive('callEvent')->times(3);
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
