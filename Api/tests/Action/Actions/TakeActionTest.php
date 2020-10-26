<?php


namespace Mush\Test\Action\Actions;

use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleService;
use Mush\Game\Service\GameConfigServiceInterface;
use \Mockery;
use Mush\Item\Entity\Item;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TakeActionTest extends TestCase
{
    /** @var ItemServiceInterface | Mockery\Mock */
    private ItemServiceInterface $itemService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;
    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $this->itemService = Mockery::mock(ItemServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->action = new Take($this->itemService, $this->playerService, $gameConfigService);
    }

    public function testExecute()
    {
        $room = new Room();
        $item = new Item();
        $item
            ->setRoom($room)
            ->setIsMovable(true)
            ->setIsHeavy(false)
        ;

        $this->gameConfig->setMaxItemInInventory(3);
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($item);
        $player = new Player();
        $player->setRoom($room);

        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($room->getItems());
        $this->assertCount(1, $player->getItems());

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEmpty($room->getItems());
        $this->assertCount(1, $player->getItems());
    }
}
