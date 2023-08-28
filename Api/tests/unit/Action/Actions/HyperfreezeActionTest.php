<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Hyperfreeze;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;

class HyperfreezeActionTest extends AbstractActionTest
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::HYPERFREEZE, 1);
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);

        $this->action = new Hyperfreeze(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->gameEquipmentService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testExecuteFruit()
    {
        // fruit
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $room->setDaedalus($player->getDaedalus());

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem($room);
        $ration = new ItemConfig();
        $ration
             ->setMechanics(new ArrayCollection([$rationType]))
             ->setEquipmentName('fruit')
        ;
        $gameRation
            ->setEquipment($ration)
            ->setName('fruit')
        ;

        $gameSuperfreezer = new GameItem($room);
        $superfreezer = new ItemConfig();
        $superfreezer->setEquipmentName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setEquipment($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
            ->setHolder($room)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (AbstractGameEvent $event) => $event instanceof StatusEvent
                && $event->getStatusName() === EquipmentStatusEnum::FROZEN
                && $event->getStatusHolder() === $gameRation)
            ->once()
        ;

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(0, $player->getEquipments());
        $this->assertEquals($gameRation->getName(), $room->getEquipments()->first()->getName());
        $this->assertCount(0, $player->getStatuses());
    }

    public function testExecuteSteak()
    {
        // Alien Steak
        $room = new Place();

        $player = $this->createPlayer(new Daedalus(), $room);

        $rationType = new Ration();
        $rationType->setIsPerishable(true);

        $gameRation = new GameItem($room);
        $ration = new ItemConfig();
        $ration
             ->setMechanics(new ArrayCollection([$rationType]))
             ->setEquipmentName(GameRationEnum::ALIEN_STEAK)
        ;
        $gameRation
            ->setEquipment($ration)
            ->setName(GameRationEnum::ALIEN_STEAK)
        ;

        $gameSuperfreezer = new GameItem($room);
        $superfreezer = new ItemConfig();
        $superfreezer->setEquipmentName(ToolItemEnum::SUPERFREEZER);
        $gameSuperfreezer
            ->setEquipment($superfreezer)
            ->setName(ToolItemEnum::SUPERFREEZER)
        ;

        $this->action->loadParameters($this->actionEntity, $player, $gameRation);

        $gameStandardRation = new GameItem(new Place());
        $standardRation = new ItemConfig();
        $standardRation
             ->setEquipmentName(GameRationEnum::STANDARD_RATION)
        ;
        $gameStandardRation
            ->setEquipment($standardRation)
            ->setName(GameRationEnum::STANDARD_RATION)
        ;

        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->gameEquipmentService->shouldReceive('transformGameEquipmentToEquipmentWithName')->once();

        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertCount(2, $room->getEquipments());
        $this->assertCount(0, $gameSuperfreezer->getStatuses());
        $this->assertCount(0, $player->getStatuses());
    }
}
