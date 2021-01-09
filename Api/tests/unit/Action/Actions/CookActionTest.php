<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Cook;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CookActionTest extends AbstractActionTest
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    private GameConfig $gameConfig;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->playerService = Mockery::mock(PlayerServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->actionEntity = $this->createActionEntity(ActionEnum::COOK, 1);

        $this->action = new Cook(
            $this->eventDispatcher,
            $this->gameEquipmentService,
            $this->playerService,
            $this->statusService,
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

    public function testCannotExecute()
    {
        $room = new Room();

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setRoom($room)
            ->setName('ration')
        ;

        $gameKitchen = new GameEquipment();
        $kitchen = new EquipmentConfig();
        $kitchen->setName(EquipmentEnum::KITCHEN);
        $gameKitchen
            ->setEquipment($kitchen)
            ->setName(EquipmentEnum::KITCHEN)
            ->setRoom($room)
        ;

        $player = $this->createPlayer(new Daedalus(), $room);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        //not possible to cook (not frozen nor standard ration)
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        $frozenStatus = new Status();
        $frozenStatus
             ->setName(EquipmentStatusEnum::FROZEN)
             ->setGameEquipment($gameRation);
        $gameRation->addStatus($frozenStatus);

        $gameKitchen->setRoom(null);
        //No microwave in the room
        $this->gameEquipmentService->shouldReceive('getOperationalEquipmentsByName')->andReturn(new ArrayCollection([]))->once();
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        //frozen fruit
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameRation = new GameItem();
        $ration = new EquipmentConfig();
        $ration->setName('ration');
        $gameRation
            ->setEquipment($ration)
            ->setPlayer($player)
            ->setName('ration')
        ;

        $frozenStatus = new Status();
        $frozenStatus
             ->setName(EquipmentStatusEnum::FROZEN)
             ->setGameEquipment($gameRation);
        $gameRation->addStatus($frozenStatus);

        $gameKitchen = new GameEquipment();
        $kitchen = new ItemConfig();
        $kitchen->setName(EquipmentEnum::KITCHEN);
        $gameKitchen
            ->setEquipment($kitchen)
            ->setName(EquipmentEnum::KITCHEN)
            ->setRoom($room)
        ;

        $this->gameConfig->setMaxItemInInventory(3);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->gameEquipmentService->shouldReceive('getOperationalEquipmentsByName')->andReturn(new ArrayCollection([$gameKitchen]))->once();
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->playerService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('delete')->once();
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $player->getItems());
        $this->assertCount(0, $player->getItems()->first()->getStatuses());
        $this->assertEquals($gameRation->getName(), $player->getItems()->first()->getName());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());

        //Standard Ration
        $daedalus = new Daedalus();
        $room = new Room();

        $gameRation = new GameItem();
        $ration = new ItemConfig();
        $ration->setName(GameRationEnum::STANDARD_RATION);
        $gameRation
            ->setEquipment($ration)
            ->setRoom($room)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $gameKitchen = new GameEquipment();
        $kitchen = new EquipmentConfig();
        $kitchen->setName(EquipmentEnum::KITCHEN);
        $gameKitchen
            ->setEquipment($kitchen)
            ->setName(EquipmentEnum::KITCHEN)
            ->setRoom($room)
        ;
        $player = $this->createPlayer(new Daedalus(), $room);

        $this->gameConfig->setMaxItemInInventory(3);
        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameRation);
        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $gameCookedRation = new GameItem();
        $cookedRation = new ItemConfig();
        $cookedRation
             ->setName(GameRationEnum::COOKED_RATION)
         ;
        $gameCookedRation
            ->setEquipment($cookedRation)
            ->setName(GameRationEnum::COOKED_RATION)
        ;

        $this->gameEquipmentService->shouldReceive('delete');
        $this->gameEquipmentService
            ->shouldReceive('getOperationalEquipmentsByName')
            ->andReturn(new ArrayCollection([$gameKitchen]))
            ->once()
        ;
        $this->gameEquipmentService->shouldReceive('createGameEquipmentFromName')->andReturn($gameCookedRation)->once();
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch');
        $this->gameEquipmentService->shouldReceive('persist');
        $this->playerService->shouldReceive('persist');
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
        $this->assertCount(0, $player->getStatuses());
        $this->assertEquals(9, $player->getActionPoint());
    }
}
