<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\LieDown;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LieDownActionTest extends TestCase
{
    /** @var RoomLogServiceInterface | Mockery\Mock */
    private RoomLogServiceInterface $roomLogService;
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
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
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new LieDown(
            $eventDispatcher,
            $this->roomLogService,
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
        $daedalus=new Daedalus();
        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = new GameEquipment();
        $tool= new Tool();
        $tool->setActions([ActionEnum::LIE_DOWN]);
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setRoom($room)
            ->setName(EquipmentEnum::BED)
        ;

        $status=new Status();
        $status
            ->setName(PlayerStatusEnum::LYING_DOWN);




        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameEquipment);

        $this->action->loadParameters($player, $actionParameter);

        //Bed already occupied
        $status->setGameEquipment($gameEquipment);
        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);


        //player already lying down
        $status->setGameEquipment(null);
        $status->setPlayer($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus=new Daedalus();
        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = new GameEquipment();
        $tool= new Tool();
        $tool->setActions([ActionEnum::LIE_DOWN]);
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setRoom($room)
            ->setName(EquipmentEnum::BED)
        ;


        $actionParameter = new ActionParameters();
        $actionParameter->setEquipment($gameEquipment);

        $this->action->loadParameters($player, $actionParameter);


        $this->statusService->shouldReceive('persist');
        $this->roomLogService->shouldReceive('createPlayerLog')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(1, $player->getStatuses());
        $this->assertCount(1, $room->getEquipments()->first()->getStatuses());
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
