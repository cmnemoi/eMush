<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Write;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Enum\ToolItemEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WriteActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var SuccessRateServiceInterface | Mockery\Mock */
    private SuccessRateServiceInterface $successRateService;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->roomLogService = Mockery::mock(RoomLogServiceInterface::class);
        $this->gameItemService = Mockery::mock(GameItemServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Write(
            $eventDispatcher,
            $this->roomLogService,
            $this->gameItemService,
            $this->playerService,
            $gameConfigService,
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
        $daedalus = new Daedalus();
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameItem();
        $item = new Item();
        $item
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
        ;
        $gameItem
            ->setItem($item)
            ->setRoom($room)
            ->setName(ToolItemEnum::BLOCK_OF_POST_IT)
        ;


        $this->gameConfig->setMaxItemInInventory(3);

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $actionParameter->setMessage('Hello world');
        $this->action->loadParameters($player, $actionParameter);


        $postIt = new Item();
        $gamePostIt = new GameItem();
        $gamePostIt->setItem($postIt);

        $this->gameItemService->shouldReceive('createGameItemFromName')->andReturn($gamePostIt)->once();
        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->gameItemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(1, $player->getItems());
        $this->assertCount(1, $player->getItems()->first()->getStatuses());
        $this->assertEquals('Hello world', $player->getItems()->first()->getStatuses()->first()->getContent());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }

    private function createPlayer(Daedalus $daedalus, Room $room): Player
    {
        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setDaedalus($daedalus)
            ->setRoom($room)
        ;

        return $player;
    }
}
