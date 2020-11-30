<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\TreatPlant;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
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
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TreatPlantActionTest extends TestCase
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
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new TreatPlant(
            $eventDispatcher,
            $this->roomLogService,
            $this->itemService,
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

    public function testCannotExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();

        $gameItem = new GameItem();
        $item = new Item();
        $gameItem
            ->setItem($item)
            ->setRoom($room)
        ;

        $plant = new Plant();

        $diseased = new Status();
        $diseased
            ->setName(ItemStatusEnum::PLANT_DISEASED)
        ;

        $gameItem->addStatus($diseased);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($player, $actionParameter);

        //Not a plant
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $item->setTypes(new ArrayCollection([$plant]));

        //Not thirsty
        $gameItem->removeStatus($diseased);
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();

        $gameItem = new GameItem();
        $item = new Item();
        $gameItem
              ->setItem($item)
              ->setRoom($room)
        ;

        $plant = new Plant();
        $item->setTypes(new ArrayCollection([$plant]));

        $diseased = new Status();
        $diseased
            ->setName(ItemStatusEnum::PLANT_DISEASED)
        ;

        $gameItem->addStatus($diseased);

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);
        $this->action->loadParameters($player, $actionParameter);

        $this->roomLogService->shouldReceive('createItemLog')->once();
        $this->itemService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getItems());
        $this->assertCount(0, $room->getItems()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(8, $player->getActionPoint());
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
