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

        $unstable = new Status(new GameItem());
        $unstable->setName(EquipmentStatusEnum::UNSTABLE);
        $hazardous = new Status(new GameItem());
        $hazardous->setName(EquipmentStatusEnum::HAZARDOUS);
        $decomposing = new Status(new GameItem());
        $decomposing->setName(EquipmentStatusEnum::DECOMPOSING);

        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createCoreStatus')->andReturn($unstable)->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());

        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createCoreStatus')->andReturn($hazardous)->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());

        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createCoreStatus')->andReturn($decomposing)->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());
    }
}
