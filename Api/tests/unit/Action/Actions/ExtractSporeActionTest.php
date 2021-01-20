<?php

namespace Mush\Test\Action\Actions;

use Mockery;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\ExtractSpore;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ExtractSporeActionTest extends AbstractActionTest
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::EXTRACT_SPORE, 2);

        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->action = new ExtractSpore(
            $this->eventDispatcher,
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

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $mushStatus = new ChargeStatus($player);
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH);

        $sporeStatus = new ChargeStatus($player);
        $sporeStatus
            ->setCharge(2)
            ->setName(PlayerStatusEnum::SPORES);

        //Player not mush
        $result = $this->action->execute();
        $this->assertInstanceOf(Error::class, $result);

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

        $mushStatus = new ChargeStatus($player);
        $mushStatus
            ->setCharge(0)
            ->setName(PlayerStatusEnum::MUSH)
        ;
        $sporeStatus = new ChargeStatus($player);
        $sporeStatus
            ->setCharge(1)
            ->setName(PlayerStatusEnum::SPORES)
        ;

        $actionParameter = new ActionParameters();

        $this->action->loadParameters($this->actionEntity, $player, $actionParameter);

        $this->statusService->shouldReceive('persist')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $player->getStatuses());
        $this->assertEquals(2, $player->getStatusByName(PlayerStatusEnum::SPORES)->getCharge());
        $this->assertEquals(8, $player->getActionPoint());
    }
}
