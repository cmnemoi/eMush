<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\ReadBook;
use Mush\Action\Entity\ActionParameters;
use Mush\Game\Enum\SkillEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Entity\Items\Book;
use Mush\Equipment\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReadBookActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
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

        $this->action = new ReadBook(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
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
        $room = new Room();
        $gameItem = new GameItem();
        $item = new Item();
        $book = new Book();
        $book->setSkill(SkillEnum::PILOT);
        $item->setTypes(new ArrayCollection([$book]));
        $gameItem
            ->setItem($item)
            ->setRoom($room)
        ;

        $this->roomLogService->shouldReceive('createItemLog')->once();

        $this->itemService->shouldReceive('delete');
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $player = new Player();
        $player
            ->setActionPoint(10)
            ->setMovementPoint(10)
            ->setMoralPoint(10)
            ->setRoom($room)
        ;

        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($room->getItems());
        $this->assertEmpty($player->getItems());
        $this->assertContains(SkillEnum::PILOT, $player->getSkills());
    }
}
