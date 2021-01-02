<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Drop;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DropActionTest extends TestCase
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Drop(
            $eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $this->statusService
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
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item
            ->setName('itemName')
            ->setIsDropable(true)
            ->setIsHeavy(false)
        ;

        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem
            ->setName('itemName')
            ->setPlayer($player)
        ;
        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertCount(1, $room->getEquipments());

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertCount(1, $room->getEquipments());
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
