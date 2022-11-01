<?php

namespace Mush\Test\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\ActionResult\CriticalFail;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\OneShot;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\Shoot;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Place\Entity\Place;

class ShootActionTest extends AbstractActionTest
{
    private RandomServiceInterface|Mockery\Mock $randomService;

    private PlayerDiseaseServiceInterface|Mockery\Mock $playerDiseaseService;

    /**
     * @before
     */
    public function before()
    {
        parent::before();

        $this->actionEntity = $this->createActionEntity(ActionEnum::SHOOT);

        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->playerDiseaseService = Mockery::mock(PlayerDiseaseServiceInterface::class);

        $this->action = new Shoot(
            $this->eventService,
            $this->actionService,
            $this->validator,
            $this->randomService,
            $this->playerDiseaseService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
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
            ->setCriticalSucessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(0)
        ;

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
            ->setHolder($player)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        // Check Success
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(100)->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(0)->once();

        // One Shot Check
        $this->eventService->shouldReceive('callEvent')->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(200)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Critical Success Check
        $this->eventService->shouldReceive('callEvent')->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(200)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Apply Effect
        $this->randomService->shouldReceive('getSingleRandomElementFromProbaArray')->andReturn(1)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Post Action
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

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
            ->setCriticalSucessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(0)
        ;

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
            ->setHolder($player)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        // Check Success
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(0)->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(100)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Critical Fail Check
        $this->eventService->shouldReceive('callEvent')->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(200)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Post Action
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
            ->setCriticalSucessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(100)
        ;

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
            ->setHolder($player)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        // Check Success
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(100)->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(0)->once();

        // One Shot Check
        $this->eventService->shouldReceive('callEvent')->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(0)->once();

        // Apply Effect
        $this->eventService->shouldReceive('callEvent')->once();

        // Post Action
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

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
            ->setCriticalSucessRate(0)
            ->setBaseDamageRange([1 => 100])
            ->setOneShotRate(0)
        ;

        $gameItem = new GameItem();
        $item = new ItemConfig();
        $item->setMechanics(new ArrayCollection([$mechanic]));
        $gameItem->setEquipment($item);
        $gameItem
            ->setName(ItemEnum::BLASTER)
            ->setHolder($player)
        ;

        $item->setActions(new ArrayCollection([$this->actionEntity]));

        $this->action->loadParameters($this->actionEntity, $player, $otherPlayer);

        // Check Success
        $this->actionService->shouldReceive('getSuccessRate')->andReturn(0)->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(100)->once();
        $this->eventService->shouldReceive('callEvent')->once();

        // Critical Fail Check
        $this->eventService->shouldReceive('callEvent')->once();
        $this->randomService->shouldReceive('getSuccessThreshold')->andReturn(0)->once();

        // Apply Effect
        $this->playerDiseaseService->shouldReceive('handleDiseaseForCause')->once();

        // Post Action
        $this->actionService->shouldReceive('applyCostToPlayer')->andReturn($player);

        $result = $this->action->execute();

        $this->assertInstanceOf(CriticalFail::class, $result);
    }
}
