<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Write;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WriteActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::WRITE);

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $this->action = new Write(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecute()
    {
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
        ;
        $gameItem
            ->setEquipment($item)
            ->setPlace($room)
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setMessage('Hello world');
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $postIt = new ItemConfig();
        $gamePostIt = new GameItem();
        $gamePostIt->setEquipment($postIt);

        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gamePostIt)->once();
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
