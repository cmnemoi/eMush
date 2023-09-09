<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\LieDown;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class LieDownActionTest extends AbstractActionTest
{
    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::LIE_DOWN);

        $this->action = new LieDown(
            $this->eventService,
            $this->actionService,
            $this->validator,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecute()
    {
        $daedalus = new Daedalus();
        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);

        $gameEquipment = new GameEquipment($room);
        $tool = new Tool();
        $tool->setActions(new ArrayCollection([$this->actionEntity]));
        $item = new EquipmentConfig();
        $item
            ->setEquipmentName(EquipmentEnum::BED)
            ->setMechanics(new ArrayCollection([$tool]))
        ;

        $gameEquipment
            ->setEquipment($item)
            ->setName(EquipmentEnum::BED)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof StatusEvent
                && $event->getStatusName() === PlayerStatusEnum::LYING_DOWN
                && $event->getStatusHolder() === $player
                && $event->getStatusTarget() === $gameEquipment
            )
            ->once()
        ;
        $this->action->loadParameters($this->actionEntity, $player, $gameEquipment);

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(1, $room->getEquipments());
        $this->assertCount(0, $player->getStatuses());
        $this->assertCount(0, $gameEquipment->getTargetingStatuses());
        $this->assertEquals(10, $player->getActionPoint());
    }
}
