<?php

namespace Mush\Test\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\CycleHandler\RationCycleHandler;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RationCycleHandlerTest extends TestCase
{
    /** @var GameEquipmentServiceInterface|Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private RationCycleHandler $rationCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->rationCycleHandler = new RationCycleHandler(
            $this->gameEquipmentService,
            $this->eventDispatcher
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNewDay()
    {
        $fruit = new ItemConfig();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $gameFruit = new GameItem();
        $gameFruit
            ->setEquipment($fruit)
        ;

        $frozen = new Status($gameFruit, EquipmentStatusEnum::FROZEN);

        $unstable = new Status(new GameItem(), EquipmentStatusEnum::UNSTABLE);
        $hazardous = new Status(new GameItem(), EquipmentStatusEnum::HAZARDOUS);
        $decomposing = new Status(new GameItem(), EquipmentStatusEnum::DECOMPOSING);

        //frozen
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(1, $gameFruit->getStatuses());

        $gameFruit->removeStatus($frozen);

        //unfrozen day 1
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::UNSTABLE && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());

        $gameFruit->addStatus($unstable);

        //day 2
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::HAZARDOUS && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());

        $gameFruit->addStatus($hazardous);

        //day 3
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (StatusEvent $event) => $event->getStatusName() === EquipmentStatusEnum::DECOMPOSING && $event->getStatusHolder() === $gameFruit)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());
    }
}
