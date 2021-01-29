<?php

namespace Mush\Test\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\CycleHandler\RationCycleHandler;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class RationCycleHandlerTest extends TestCase
{
    /** @var GameEquipmentServiceInterface | Mockery\Mock */
    private GameEquipmentServiceInterface $gameEquipmentService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    private RationCycleHandler $rationCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentService = Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

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

        $frozen = new Status($gameFruit);
        $frozen->setName(EquipmentStatusEnum::FROZEN);

        $unstable = new Status(new GameItem());
        $unstable->setName(EquipmentStatusEnum::UNSTABLE);
        $hazardous = new Status(new GameItem());
        $hazardous->setName(EquipmentStatusEnum::HAZARDOUS);
        $decomposing = new Status(new GameItem());
        $decomposing->setName(EquipmentStatusEnum::DECOMPOSING);

        //frozen
        $this->gameEquipmentService->shouldReceive('persist')->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(1, $gameFruit->getStatuses());

        $gameFruit->removeStatus($frozen);

        //unfrozen day 1
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->with(EquipmentStatusEnum::UNSTABLE,
                $gameFruit,
                null,
                VisibilityEnum::HIDDEN
            )
            ->andReturn($unstable)
            ->once()
    ;

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());

        $gameFruit->addStatus($unstable);

        //day 2
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->with(EquipmentStatusEnum::HAZARDOUS,
                    $gameFruit,
                    null,
                    VisibilityEnum::HIDDEN
                )
            ->andReturn($hazardous)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());

        $gameFruit->addStatus($hazardous);

        //day 3
        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService
            ->shouldReceive('createCoreStatus')
            ->with(EquipmentStatusEnum::DECOMPOSING,
                    $gameFruit,
                    null,
                    VisibilityEnum::HIDDEN
                )
            ->andReturn($decomposing)
            ->once()
        ;

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
        $this->assertCount(0, $gameFruit->getStatuses());
    }
}
