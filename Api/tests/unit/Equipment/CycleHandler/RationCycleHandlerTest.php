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
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RationCycleHandlerTest extends TestCase
{
    private GameEquipmentServiceInterface|Mockery\Mock $gameEquipmentService;

    private Mockery\Mock|StatusServiceInterface $statusService;

    private RationCycleHandler $rationCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->rationCycleHandler = new RationCycleHandler(
            $this->gameEquipmentService,
            $this->statusService
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
            ->setEquipment($fruit);

        $frozenConfig = new StatusConfig();
        $frozenConfig->setStatusName(EquipmentStatusEnum::FROZEN);
        $frozen = new Status($gameFruit, $frozenConfig);

        // frozen
        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->statusService->shouldReceive('removeStatus')->never();
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        self::assertCount(1, $gameFruit->getStatuses());
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
            ->setEquipment($fruit);

        // unfrozen day 1
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->statusService->shouldReceive('removeStatus')->never();

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        self::assertCount(0, $gameFruit->getStatuses());
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
            ->setEquipment($fruit);

        $unstableConfig = new StatusConfig();
        $unstableConfig->setStatusName(EquipmentStatusEnum::UNSTABLE);
        $unstable = new Status($gameFruit, $unstableConfig);

        // day 2
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->statusService->shouldReceive('removeStatus')->once();

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
            ->setEquipment($fruit);

        $hazardousConfig = new StatusConfig();
        $hazardousConfig->setStatusName(EquipmentStatusEnum::HAZARDOUS);
        $hazardous = new Status($gameFruit, $hazardousConfig);

        // day 3
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->statusService->shouldReceive('createStatusFromName')->once();
        $this->statusService->shouldReceive('removeStatus')->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        self::assertCount(1, $gameFruit->getStatuses());
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
            ->setEquipment($fruit);

        $decomposingConfig = new StatusConfig();
        $decomposingConfig->setStatusName(EquipmentStatusEnum::DECOMPOSING);
        $decomposing = new Status($gameFruit, $decomposingConfig);

        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->statusService->shouldReceive('removeStatus')->never();
        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        self::assertCount(1, $gameFruit->getStatuses());
    }
}
