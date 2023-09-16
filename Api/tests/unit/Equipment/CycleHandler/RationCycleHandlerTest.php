<?php

namespace Mush\Tests\unit\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\CycleHandler\RationCycleHandler;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;

class RationCycleHandlerTest extends TestCase
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    private EventServiceInterface|Mockery\Mock $eventService;

    private RationCycleHandler $rationCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->eventService = \Mockery::mock(EventServiceInterface::class);

        $this->rationCycleHandler = new RationCycleHandler(
            $this->gameEquipmentService,
            $this->eventService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testNewDayFrozen()
    {
        $fruit = new ItemConfig();

        $place = new Place();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $place->setDaedalus($daedalus);
        $gameFruit = new GameItem($place);
        $gameFruit
            ->setEquipment($fruit)
        ;

        $frozenConfig = new StatusConfig();
        $frozenConfig->setStatusName(EquipmentStatusEnum::FROZEN);
        $frozen = new Status($gameFruit, $frozenConfig);

        // frozen
        $this->eventService->shouldReceive('callEvent')->never();
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(1, $gameFruit->getStatuses());
    }

    public function testNewDayFresh()
    {
        $fruit = new ItemConfig();

        $place = new Place();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $place->setDaedalus($daedalus);
        $gameFruit = new GameItem($place);
        $gameFruit
            ->setEquipment($fruit)
        ;

        // unfrozen day 1
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::UNSTABLE && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());
    }

    public function testNewDayUnstable()
    {
        $fruit = new ItemConfig();

        $place = new Place();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $place->setDaedalus($daedalus);
        $gameFruit = new GameItem($place);
        $gameFruit
            ->setEquipment($fruit)
        ;

        $unstableConfig = new StatusConfig();
        $unstableConfig->setStatusName(EquipmentStatusEnum::UNSTABLE);
        $unstable = new Status($gameFruit, $unstableConfig);

        // day 2
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::HAZARDOUS && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;
        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::UNSTABLE && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
    }

    public function testNewDayHazardous()
    {
        $fruit = new ItemConfig();

        $place = new Place();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $place->setDaedalus($daedalus);
        $gameFruit = new GameItem($place);
        $gameFruit
            ->setEquipment($fruit)
        ;

        $hazardousConfig = new StatusConfig();
        $hazardousConfig->setStatusName(EquipmentStatusEnum::HAZARDOUS);
        $hazardous = new Status($gameFruit, $hazardousConfig);

        // day 3
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::DECOMPOSING && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::HAZARDOUS && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(1, $gameFruit->getStatuses());
    }

    public function testNewDayDecomposing()
    {
        $fruit = new ItemConfig();

        $place = new Place();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $place->setDaedalus($daedalus);
        $gameFruit = new GameItem($place);
        $gameFruit
            ->setEquipment($fruit)
        ;

        $decomposingConfig = new StatusConfig();
        $decomposingConfig->setStatusName(EquipmentStatusEnum::DECOMPOSING);
        $decomposing = new Status($gameFruit, $decomposingConfig);

        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->eventService->shouldReceive('callEvent')->never();
        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        $this->assertCount(1, $gameFruit->getStatuses());
    }
}
