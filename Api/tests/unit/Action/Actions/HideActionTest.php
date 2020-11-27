<?php

namespace Mush\Test\Action\Actions;


use Doctrine\Common\Collections\ArrayCollection;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Hide;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HideActionTest extends TestCase
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
        $this->itemService = Mockery::mock(GameItemServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Hide(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
            $this->playerService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCannotExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();

        $gameItem = new GameItem();
        $item = new Item();
        $item->setIsHideable(true);
        $gameItem
            ->setItem($item)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($player, $actionParameter);

        //item is not in the room
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //item is not hideable
        $gameItem->setRoom($room);
        $item->setIsHideable(false);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }


    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameItem();
        $item = new Item();
        $item->setIsHideable(true);
        $gameItem
            ->setItem($item)
            ->setPlayer($player)
        ;

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($player, $actionParameter);


        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');


        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(0, $player->getItems());
        $this->assertCount(1, $room->getItems()->first()->getStatuses());
        $this->assertCount(1, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());
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
