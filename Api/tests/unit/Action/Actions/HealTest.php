<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Heal;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class HealTest extends AbstractActionTest
{
    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createActionEntity(ActionEnum::HEAL);
        $this->actionConfig->setOutputQuantity(3);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->actionHandler = new Heal(
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
        $targetPlayer = $this->createPlayer(new Daedalus(), $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $targetPlayer, ActionVariableEnum::OUTPUT_QUANTITY, $this->actionHandler->getTags())
            ->andReturn(3)
            ->once();
        $this->eventService->shouldReceive('callEvent');
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
