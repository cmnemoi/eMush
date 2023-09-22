<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\Flirt;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\User\Entity\User;

class FlirtActionTest extends AbstractActionTest
{
    private PlayerServiceInterface|Mockery\Mock $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::FLIRT);

        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Flirt(
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
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $targetPlayer = $this->createPlayer($daedalus, $room);
        $characterConfig = new CharacterConfig();
        $characterConfig->setCharacterName('playerOne');
        new PlayerInfo($targetPlayer, new User(), $characterConfig);

        $room->setDaedalus($player->getDaedalus());

        $this->action->loadParameters($this->actionEntity, $player, $targetPlayer);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->playerService->shouldReceive('persist')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertTrue($player->HasFlirtedWith($targetPlayer));
    }
}
