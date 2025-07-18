<?php

namespace Mush\Tests\unit\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Enum\NeronFoodDestructionEnum;
use Mush\Equipment\CycleHandler\RationCycleHandler;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
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

    private DeleteEquipmentServiceInterface|Mockery\Mock $deleteEquipmentService;

    private RationCycleHandler $rationCycleHandler;

    private GameConfig $gameConfig;
    private LocalizationConfig $localizationConfig;
    private Daedalus $daedalus;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->gameEquipmentService = \Mockery::mock(GameEquipmentServiceInterface::class);
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);
        $this->deleteEquipmentService = \Mockery::mock(DeleteEquipmentServiceInterface::class);

        $this->rationCycleHandler = new RationCycleHandler(
            $this->gameEquipmentService,
            $this->statusService,
            $this->deleteEquipmentService
        );

        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $this->daedalus = new Daedalus();
        $this->daedalus->setDaedalusInfo(new DaedalusInfo($this->daedalus, $gameConfig, $localizationConfig));
        $this->daedalus->getDaedalusInfo()->setNeron(new Neron());
        $this->daedalus->getDaedalusInfo()->getNeron()->changeFoodDestructionOption(NeronFoodDestructionEnum::NEVER);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testNewDayFrozen()
    {
        $fruit = new ItemConfig();

        $place = new Place();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $place->setDaedalus($this->daedalus);
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

        $place->setDaedalus($this->daedalus);
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

        $place->setDaedalus($this->daedalus);
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

        $place->setDaedalus($this->daedalus);
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

        $place->setDaedalus($this->daedalus);
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

    public function testNewDayDecomposingWithBiosOptionOnDecomposing()
    {
        $this->daedalus->getDaedalusInfo()->getNeron()->changeFoodDestructionOption(NeronFoodDestructionEnum::DECOMPOSING);
        $fruit = new ItemConfig();

        $place = new Place();

        $fruitType = new Fruit();
        $fruit->setMechanics(new ArrayCollection([$fruitType]));

        $place->setDaedalus($this->daedalus);
        $gameFruit = new GameItem($place);
        $gameFruit
            ->setEquipment($fruit);

        $decomposingConfig = new StatusConfig();
        $decomposingConfig->setStatusName(EquipmentStatusEnum::DECOMPOSING);
        $decomposing = new Status($gameFruit, $decomposingConfig);

        $this->gameEquipmentService->shouldReceive('persist')->once();
        $this->statusService->shouldReceive('createStatusFromName')->never();
        $this->statusService->shouldReceive('removeStatus')->never();
        $this->deleteEquipmentService->shouldReceive('execute')->once();
        $this->rationCycleHandler->handleNewDay($gameFruit, new \DateTime());
        self::assertCount(1, $gameFruit->getStatuses());
    }
}
