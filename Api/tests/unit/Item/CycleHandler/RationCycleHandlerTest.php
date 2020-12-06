<?php

namespace Mush\Test\Item\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\CycleHandler\RationCycleHandler;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Entity\Items\Fruit;
use Mush\Equipment\Service\GameItemServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class RationCycleHandlerTest extends TestCase
{
    /** @var GameItemServiceInterface | Mockery\Mock */
    private GameItemServiceInterface $itemService;
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;

    private RationCycleHandler $rationCycleHandler;

    /**
     * @before
     */
    public function before()
    {
        $this->itemService = Mockery::mock(GameItemServiceInterface::class);
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->rationCycleHandler = new RationCycleHandler(
            $this->itemService,
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
        $fruit = new Item();

        $fruitType = new Fruit();
        $fruit->setTypes(new ArrayCollection([$fruitType]));

        $daedalus = new Daedalus();
        $gameFruit = new GameItem();
        $gameFruit
            ->setItem($fruit)
        ;

        $unstable = new Status();
        $unstable->setName(ItemStatusEnum::UNSTABLE);
        $hazardous = new Status();
        $hazardous->setName(ItemStatusEnum::HAZARDOUS);
        $decomposing = new Status();
        $decomposing->setName(ItemStatusEnum::DECOMPOSING);

        $this->itemService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createCoreItemStatus')->andReturn($unstable)->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());

        $this->assertContains($unstable, $gameFruit->getStatuses());
        $this->assertNotContains($hazardous, $gameFruit->getStatuses());
        $this->assertNotContains($decomposing, $gameFruit->getStatuses());

        $this->itemService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createCoreItemStatus')->andReturn($hazardous)->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());

        $this->assertNotContains($unstable, $gameFruit->getStatuses());
        $this->assertContains($hazardous, $gameFruit->getStatuses());
        $this->assertNotContains($decomposing, $gameFruit->getStatuses());

        $this->itemService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createCoreItemStatus')->andReturn($decomposing)->once();

        $this->rationCycleHandler->handleNewDay($gameFruit, $daedalus, new \DateTime());

        $this->assertNotContains($unstable, $gameFruit->getStatuses());
        $this->assertNotContains($hazardous, $gameFruit->getStatuses());
        $this->assertContains($decomposing, $gameFruit->getStatuses());
    }
}
