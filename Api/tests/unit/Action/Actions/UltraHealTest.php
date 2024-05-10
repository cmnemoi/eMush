<?php

namespace Mush\Tests\unit\Action\Actions;

use Mockery;
use Mush\Action\Actions\UltraHeal;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Place\Entity\Place;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;

/**
 * @internal
 */
final class UltraHealTest extends AbstractActionTest
{
    /** @var Mockery\Mock|PlayerServiceInterface */
    private PlayerServiceInterface $playerService;

    /** @var Mockery\Mock|PlayerVariableServiceInterface */
    private PlayerVariableServiceInterface $playerVariableService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->createActionEntity(ActionEnum::ULTRAHEAL);
        $this->playerService = \Mockery::mock(PlayerServiceInterface::class);
        $this->playerVariableService = \Mockery::mock(PlayerVariableServiceInterface::class);

        $this->actionHandler = new UltraHeal(
            $this->eventService,
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
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $gameItem = new GameItem($room);
        $item = new ItemConfig();
        $gameItem
            ->setName('item')
            ->setEquipment($item);
        $player = $this->createPlayer($daedalus, $room);

        $this->playerVariableService
            ->shouldReceive('setPlayerVariableToMax')
            ->with($player, PlayerVariableEnum::HEALTH_POINT);
        $this->playerService->shouldReceive('persist');

        $this->actionHandler->loadParameters($this->actionConfig, $this->actionProvider, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent');
        $this->eventService->shouldReceive('callEvent');
        $result = $this->actionHandler->execute();

        self::assertInstanceOf(Success::class, $result);
    }
}
