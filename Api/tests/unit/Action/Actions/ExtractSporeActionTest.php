<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Action;
use Mush\Action\Actions\ExtractSpore;
use Mush\Action\Entity\ActionParameters;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExtractSporeActionTest extends TestCase
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private Action $action;

    /**
     * @before
     */
    public function before()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $eventDispatcher->shouldReceive('dispatch');

        $this->action = new ExtractSpore(
            $eventDispatcher,
            $this->statusService,
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
        $daedalus->setSpores(1);

        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);

        $actionParameter = new ActionParameters();

        $this->action->loadParameters($player, $actionParameter);

        $mushStatus = new ChargeStatus();
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH);

        $sporeStatus = new ChargeStatus();
        $sporeStatus
            ->setCharge(2)
            ->setName(PlayerStatusEnum::SPORES);

        //Player not mush
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //player already max spores
        $mushStatus->setPlayer($player);
        $sporeStatus->setPlayer($player);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

        //No spores availlable
        $daedalus->setSpores(0);
        $sporeStatus
            ->setCharge(1);

        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $daedalus->setSpores(1);

        $room = new Room();

        $player = $this->createPlayer($daedalus, $room);

        $mushStatus = new ChargeStatus();
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH)
            ->setPlayer($player);

        $sporeStatus = new ChargeStatus();
        $sporeStatus
            ->setCharge(1)
            ->setPlayer($player)
            ->setName(PlayerStatusEnum::SPORES);

        $actionParameter = new ActionParameters();

        $this->action->loadParameters($player, $actionParameter);

        $this->statusService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createCorePlayerStatus')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $player->getStatuses());
        $this->assertEquals(2, $player->getStatusByName(PlayerStatusEnum::SPORES)->getCharge());
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
