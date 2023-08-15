<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Comfort;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;

class ComfortActionTest extends AbstractActionTest
{
    private PlayerServiceInterface|Mockery\Mock $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::COMFORT);
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Comfort(
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

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
