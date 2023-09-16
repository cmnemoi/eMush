<?php

namespace Mush\Tests\unit\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\CriticalFail;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\OneShot;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Shoot;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Event\ActionVariableEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Place\Entity\Place;

class ShootActionTest extends AbstractActionTest
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    private EventModifierServiceInterface|Mockery\Mock $modifierService;

    private DiseaseCauseServiceInterface|Mockery\Mock $diseaseCauseService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SHOOT);

        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->diseaseCauseService = \Mockery::mock(DiseaseCauseServiceInterface::class);

        $this->action = new Shoot(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->diseaseCauseService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testSuccessful()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $otherPlayer = $this->createPlayer($daedalus, $room);

        $mechanic = new Weapon();
        $mechanic
            ->setCriticalFailRate(0)
            ->setCriticalSuccessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(0)
        ;

        $gameItem = new GameItem($player);
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        $actionVariableEventCritical = new ActionVariableEvent(
            $this->actionEntity,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            $mechanic->getCriticalSuccessRate(),
            $player,
            $otherPlayer
        );

        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $otherPlayer, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(100)
            ->once()
        ;
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEventCritical)
            ->twice()
        ;
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->twice(); // critical events
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')->andReturn(1)->once();
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->eventService->shouldReceive('callEvent')->once();
        $result = $this->action->execute();

        $this->assertInstanceOf(Success::class, $result);
    }

    public function testFail()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $otherPlayer = $this->createPlayer($daedalus, $room);

        $mechanic = new Weapon();
        $mechanic
            ->setCriticalFailRate(0)
            ->setCriticalSuccessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(0)
        ;

        $gameItem = new GameItem($player);
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        $actionVariableEventCritical = new ActionVariableEvent(
            $this->actionEntity,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            $mechanic->getCriticalSuccessRate(),
            $player,
            $otherPlayer
        );
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEventCritical)
            ->once()
        ;
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $otherPlayer, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(100)
            ->once()
        ;
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->twice();
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(Fail::class, $result);
    }

    public function testOneShot()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $otherPlayer = $this->createPlayer($daedalus, $room);

        $mechanic = new Weapon();
        $mechanic
            ->setCriticalFailRate(0)
            ->setCriticalSuccessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(100)
        ;

        $gameItem = new GameItem($player);
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        $actionVariableEventCritical = new ActionVariableEvent(
            $this->actionEntity,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            $mechanic->getOneShotRate(),
            $player,
            $otherPlayer
        );

        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $otherPlayer, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(100)
            ->once()
        ;
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEventCritical)
            ->once()
        ;
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(true)->twice();
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaCollection')->andReturn(1)->once();
        $this->eventService->shouldReceive('callEvent')->once();
        $result = $this->action->execute();

        $this->assertInstanceOf(OneShot::class, $result);
    }

    public function testCriticalFail()
    {
        $daedalus = new Daedalus();

        $room = new Place();

        $player = $this->createPlayer($daedalus, $room);
        $otherPlayer = $this->createPlayer($daedalus, $room);

        $mechanic = new Weapon();
        $mechanic
            ->setCriticalFailRate(100)
            ->setCriticalSuccessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(0)
        ;

        $gameItem = new GameItem($player);
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        $actionVariableEventCritical = new ActionVariableEvent(
            $this->actionEntity,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            100,
            $player,
            $otherPlayer
        );
        $this->actionService->shouldReceive('getActionModifiedActionVariable')
            ->with($player, $this->actionEntity, $otherPlayer, ActionVariableEnum::PERCENTAGE_SUCCESS)
            ->andReturn(0)
            ->once()
        ;
        $this->eventService
            ->shouldReceive('computeEventModifications')
            ->andReturn($actionVariableEventCritical)
            ->once()
        ;
        $this->randomService->shouldReceive('isSuccessful')->with(0)->andReturn(false)->once();
        $this->randomService->shouldReceive('isSuccessful')->with(100)->andReturn(true)->once();
        $this->diseaseCauseService->shouldReceive('handleDiseaseForCause')->once();
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);
        $result = $this->action->execute();

        $this->assertInstanceOf(CriticalFail::class, $result);
    }
}
