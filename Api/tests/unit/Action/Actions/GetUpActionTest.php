<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\GetUp;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetUpActionTest extends TestCase
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private AbstractAction $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new GetUp(
            $eventDispatcher,
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

        $player = $this->createPlayer($daedalus, $room);
        $player2 = $this->createPlayer($daedalus, $room);

        $gameItem = new GameEquipment();
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
        ;
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
            ->setName(EquipmentEnum::BED)
        ;

        $status = new Status();
        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setPlayer($player2)
            ->setGameEquipment($gameItem);

        $actionParameter = new ActionParameters();

        $this->action->loadParameters($player, $actionParameter);

        $result = $this->action->execute();

        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $room = new Room();

        $player = $this->createPlayer(new Daedalus(), $room);

        $gameItem = new GameEquipment();
        $item = new EquipmentConfig();
        $item
            ->setName(EquipmentEnum::BED)
        ;
        $gameItem
            ->setEquipment($item)
            ->setRoom($room)
            ->setName(EquipmentEnum::BED)
        ;

        $status = new Status();
        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setPlayer($player)
            ->setGameEquipment($gameItem);

        $actionParameter = new ActionParameters();

        $this->action->loadParameters($player, $actionParameter);

        $this->statusService->shouldReceive('delete');

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertCount(0, $room->getEquipments()->first()->getStatuses());
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
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;

        return $player;
    }
}
