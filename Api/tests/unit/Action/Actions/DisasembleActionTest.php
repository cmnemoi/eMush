<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Disassemble;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;

class DisasembleActionTest extends AbstractActionTest
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->actionEntity = $this->createActionEntity(ActionEnum::DISASSEMBLE, 3);

        $this->action = new Disassemble(
            $this->eventDispatcher,
            $this->actionService,
            $this->validator,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testExecuteFail()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setName('some name')
            ->setHolder($room)
        ;

        $item
            ->setActions(new ArrayCollection([$this->actionEntity]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $attempt = new Attempt(new Player(), StatusEnum::ATTEMPT);
        $attempt
            ->setAction($this->action->getActionName())
        ;
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        //Fail try
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertEquals(0, $attempt->getCharge());
    }

    public function testExecuteSuccess()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $gameItem = new GameItem();
        $item = new ItemConfig();
        $gameItem->setEquipment($item);
        $gameItem
            ->setName('some name')
            ->setHolder($room)
        ;

        $item
            ->setActions(new ArrayCollection([$this->actionEntity]))
            ->setDismountedProducts([ItemEnum::METAL_SCRAPS => 1])
        ;

        $player = $this->createPlayer($daedalus, $room, [SkillEnum::TECHNICIAN]);

        $attempt = new Attempt(new Player(), StatusEnum::ATTEMPT);
        $attempt
            ->setAction($this->action->getActionName())
        ;
        $this->actionService->shouldReceive('getAttempt')->andReturn($attempt);

        $this->action->loadParameters($this->actionEntity, $player, $gameItem);
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(10)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $scrap = new GameItem();

        $this->eventDispatcher->shouldReceive('dispatch')->twice();

        //Success
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(0, $player->getStatuses());
    }
}
