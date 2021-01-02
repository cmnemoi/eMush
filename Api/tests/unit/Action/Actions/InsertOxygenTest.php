<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\InsertOxygen;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InsertOxygenTest extends TestCase
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var PlayerServiceInterface | Mockery\Mock */
    private PlayerServiceInterface $playerService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;
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
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new InsertOxygen(
             $eventDispatcher,
             $this->gameEquipmentService,
             $this->playerService,
             $this->statusService,
             $gameConfigService
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
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setIsDropable(false)
            ->setIsHeavy(false)
        ;

        $player = $this->createPlayer($daedalus, $room);
        $gameItem
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($player)
        ;

        $daedalus->setOxygen(32);
        $this->gameConfig->setMaxOxygen(32);

        $tank = new EquipmentConfig();
        $tank->setName(EquipmentEnum::OXYGEN_TANK);
        $gameTank = new GameEquipment();
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setRoom($room)
            ;

        $this->gameEquipmentService->shouldReceive('getOperationalEquipmentsByName')->andReturn(new ArrayCollection([$gameTank]))->once();

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Room();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);

        $item
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setIsDropable(false)
            ->setIsHeavy(false)
        ;

        $player = $this->createPlayer($daedalus, $room);
        $gameItem
            ->setName(ItemEnum::OXYGEN_CAPSULE)
            ->setPlayer($player)
        ;

        $daedalus->setOxygen(10);
        $this->gameConfig->setMaxOxygen(32);

        $tank = new EquipmentConfig();
        $tank->setName(EquipmentEnum::OXYGEN_TANK);
        $gameTank = new GameEquipment();
        $gameTank
            ->setEquipment($tank)
            ->setName(EquipmentEnum::OXYGEN_TANK)
            ->setRoom($room)
            ;

        $this->gameEquipmentService->shouldReceive('delete');
        $this->gameEquipmentService->shouldReceive('getOperationalEquipmentsByName')->andReturn(new ArrayCollection([$gameTank]))->once();

        $actionParameter = new ActionParameters();
        $actionParameter->setItem($gameItem);

        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEmpty($player->getItems());
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(10, $player->getActionPoint());
        $this->assertEquals(11, $daedalus->getOxygen());
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
