<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\Shred;
use Mush\Action\Entity\ActionParameters;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ShredActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
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
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new Shred(
            $eventDispatcher,
            $this->roomLogService,
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
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $document = new Document();
        $document->setCanShred(true);
        $item->setMechanics(new ArrayCollection([$document]));
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
        ;

        $this->roomLogService->shouldReceive('createEquipmentLog')->once();

        $this->gameEquipmentService->shouldReceive('delete');
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
        $this->assertEmpty($room->getEquipments());
        $this->assertEmpty($player->getItems());
    }
}
