<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Gag;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Service\StatusServiceInterface;
use Mush\User\Entity\User;

class GagActionTest extends AbstractActionTest
{
    /* @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface|Mockery\Mock $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::GAG, 1);

        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->action = new Gag(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->statusService
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
        $daedalus = new Daedalus();

        $player = $this->createPlayer($daedalus, $room);
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        // No item in the room
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->statusService->shouldReceive('createStatusFromName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
