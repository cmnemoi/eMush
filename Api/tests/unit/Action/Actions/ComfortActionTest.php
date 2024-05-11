<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Comfort;
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
final class ComfortActionTest extends AbstractActionTest
{
    private Mockery\Mock|PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::COMFORT);
        $this->actionConfig->setOutputQuantity(2);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->actionHandler = new Comfort(
            $this->eventService,
            $this->actionService,
            $this->validator
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
        $targetPlayer = $this->createPlayer(new Daedalus(), $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionConfig, $this->actionProvider, $targetPlayer, ActionVariableEnum::OUTPUT_QUANTITY)
            ->andReturn(2)
            ->once();
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
