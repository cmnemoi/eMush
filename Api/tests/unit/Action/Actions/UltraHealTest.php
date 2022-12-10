<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\UltraHeal;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;

class UltraHealActionTest extends AbstractActionTest
{
    /** @var PlayerServiceInterface|Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /** @var PlayerVariableServiceInterface|Mockery\Mock */
    private PlayerVariableServiceInterface $playerVariableService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::ULTRAHEAL);
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->playerVariableService = \Mockery::mock(PlayerVariableServiceInterface::class);

        $this->action = new UltraHeal(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->playerService,
            $this->playerVariableService
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
        $gameItem
            ->setName('item')
            ->setEquipment($item)
        ;
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->playerVariableService
            ->shouldReceive('setPlayerVariableToMax')
            ->with($player, PlayerVariableEnum::HEALTH_POINT);
        $this->playerService->shouldReceive('persist');

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventDispatcher->shouldReceive('dispatch');
        $this->eventDispatcher->shouldReceive('dispatch');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }
}
