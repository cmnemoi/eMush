<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Repository\ConsumableDiseaseConfigRepository;
use Mush\Disease\Repository\ConsumableDiseaseRepository;
use Mush\Disease\Service\ConsumableDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Service\RandomServiceInterface;
use PHPUnit\Framework\TestCase;

class ConsumableDiseaseServiceTest extends TestCase
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var ConsumableDiseaseRepository|Mockery\Mock */
    private ConsumableDiseaseRepository $consumableDiseaseRepository;

    /** @var ConsumableDiseaseConfigRepository|Mockery\Mock */
    private ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository;

    private ConsumableDiseaseService $consumableDiseaseService;

    /**
     * @before
     */
    public function before()
    {
        $this->consumableDiseaseRepository = \Mockery::mock(ConsumableDiseaseRepository::class);
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->consumableDiseaseService = new ConsumableDiseaseService(
            $this->consumableDiseaseRepository,
            $this->entityManager,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreateConsumableDiseasesWithPredefinedDiseases()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $disease1 = new ConsumableDiseaseAttribute();
        $disease1->setDisease('Disease 1');
        $disease2 = new ConsumableDiseaseAttribute();
        $disease2->setDisease('Disease 2');

        $consumableDiseaseConfig = new ConsumableDiseaseConfig();
        $consumableDiseaseConfig
            ->setCauseName('name')
            ->setAttributes(new ArrayCollection([$disease1, $disease2]))
        ;

        $gameConfig->addConsumableDiseaseConfig($consumableDiseaseConfig);

        $this->entityManager
            ->shouldReceive('persist')
            ->times(3)
        ;

        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $consumableDisease = $this->consumableDiseaseService->createConsumableDiseases('name', $daedalus);

        $this->assertInstanceOf(ConsumableDisease::class, $consumableDisease);
        $this->assertCount(2, $consumableDisease->getDiseases());
    }

    public function testCreateConsumableDiseasesWithMultiplePossibleDiseases()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $consumableDiseaseConfig = new ConsumableDiseaseConfig();
        $consumableDiseaseConfig
            ->setEffectNumber([1 => 1, 2 => 10])
            ->setDiseasesName(['Disease 1' => 5, 'Disease 2' => 10])
            ->setCuresName(['Disease 1' => 10])
            ->setCuresChances([30 => 1, 45 => 2])
            ->setDiseasesChances([20 => 1, 25 => 2])
            ->setDiseasesDelayMin([0 => 1])
            ->setCauseName('name')
        ;

        $gameConfig->addConsumableDiseaseConfig($consumableDiseaseConfig);

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(1, $probaCollection->toArray())
                && $probaCollection->toArray()[1] === 1
                && key_exists(2, $probaCollection->toArray())
                && $probaCollection->toArray()[2] === 10
            ))
            ->andReturn(2)
            ->times(1)
        ;

        // One cure and one disease
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->with(range(1, 3), 2)
            ->andReturn([1, 3])
            ->once()
        ;

        // first the service chose and design the cure
        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaCollection')
            ->withArgs(fn ($probaCollection, $number) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('Disease 1', $probaCollection->toArray())
                && $probaCollection->toArray()['Disease 1'] === 10
                && $number === 1
            ))
            ->andReturn(['Disease 1'])
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(30, $probaCollection->toArray())
                && $probaCollection->toArray()[30] === 1
                && key_exists(45, $probaCollection->toArray())
                && $probaCollection->toArray()[45] === 2
            ))
            ->andReturn(45)
            ->once()
        ;

        // then the disease
        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaCollection')
            ->withArgs(fn ($probaCollection, $number) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('Disease 1', $probaCollection->toArray())
                && $probaCollection->toArray()['Disease 1'] === 5
                && key_exists('Disease 2', $probaCollection->toArray())
                && $probaCollection->toArray()['Disease 2'] === 10
                && $number === 1
            ))
            ->andReturn(['Disease 1'])
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(20, $probaCollection->toArray())
                && $probaCollection->toArray()[20] === 1
                && key_exists(25, $probaCollection->toArray())
                && $probaCollection->toArray()[25] === 2
            ))
            ->andReturn(45)
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(0, $probaCollection->toArray())
                && $probaCollection->toArray()[0] === 1
            ))
            ->andReturn(0)
            ->once()
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->times(3)
        ;

        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $consumableDisease = $this->consumableDiseaseService->createConsumableDiseases('name', $daedalus);

        $this->assertInstanceOf(ConsumableDisease::class, $consumableDisease);
        $this->assertCount(1, $consumableDisease->getDiseases());
        $this->assertCount(1, $consumableDisease->getCures());
    }

    public function testCreateConsumableDiseasesWithDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $consumableDiseaseConfig = new ConsumableDiseaseConfig();
        $consumableDiseaseConfig
            ->setEffectNumber([1 => 1])
            ->setDiseasesName(['Disease 1' => 5])
            ->setCuresName([])
            ->setCuresChances([])
            ->setDiseasesChances([20 => 1])
            ->setDiseasesDelayMin([1 => 1])
            ->setDiseasesDelayLength([5 => 1, 8 => 5])
            ->setCauseName('name')
        ;

        $gameConfig->addConsumableDiseaseConfig($consumableDiseaseConfig);

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(1, $probaCollection->toArray())
                && $probaCollection->toArray()[1] === 1
            ))
            ->andReturn(1)
            ->times(1)
        ;

        // One cure and one disease
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->with(range(1, 1), 1)
            ->andReturn([1])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaCollection')
            ->withArgs(fn ($probaCollection, $number) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('Disease 1', $probaCollection->toArray())
                && $probaCollection->toArray()['Disease 1'] === 5
                && $number === 1
            ))
            ->andReturn(['Disease 1'])
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(20, $probaCollection->toArray())
                && $probaCollection->toArray()[20] === 1
            ))
            ->andReturn(20)
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(1, $probaCollection->toArray())
                && $probaCollection->toArray()[1] === 1
            ))
            ->andReturn(1)
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && $probaCollection->count() === 2
                && key_exists(5, $probaCollection->toArray())
                && $probaCollection->toArray()[5] === 1
                && key_exists(8, $probaCollection->toArray())
                && $probaCollection->toArray()[8] === 5
            ))
            ->andReturn(8)
            ->once()
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
        ;

        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $consumableDisease = $this->consumableDiseaseService->createConsumableDiseases('name', $daedalus);

        $this->assertInstanceOf(ConsumableDisease::class, $consumableDisease);
        $this->assertCount(1, $consumableDisease->getDiseases());
        /** @var ConsumableDiseaseAttribute $disease */
        $disease = $consumableDisease->getDiseases()->first();
        $this->assertEquals('Disease 1', $disease->getDisease());
        $this->assertEquals(1, $disease->getDelayMin());
        $this->assertEquals(8, $disease->getDelayLength());
    }

    public function testCreateConsumableDiseasesWithoutDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $consumableDiseaseConfig = new ConsumableDiseaseConfig();
        $consumableDiseaseConfig
            ->setEffectNumber([1 => 1])
            ->setDiseasesName(['Disease 1' => 5])
            ->setCuresName([])
            ->setCuresChances([])
            ->setDiseasesChances([100 => 1])
            ->setDiseasesDelayMin([1 => 1])
            ->setDiseasesDelayLength([5 => 1, 8 => 5])
            ->setCauseName('name')
        ;

        $gameConfig->addConsumableDiseaseConfig($consumableDiseaseConfig);

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(1, $probaCollection->toArray())
                && $probaCollection->toArray()[1] === 1
            ))
            ->andReturn(1)
            ->times(1)
        ;

        // One cure and one disease
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->with(range(1, 1), 1)
            ->andReturn([1])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaCollection')
            ->withArgs(fn ($probaCollection, $number) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('Disease 1', $probaCollection->toArray())
                && $probaCollection->toArray()['Disease 1'] === 5
                && $number === 1
            ))
            ->andReturn(['Disease 1'])
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(100, $probaCollection->toArray())
                && $probaCollection->toArray()[100] === 1
            ))
            ->andReturn(100)
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(1, $probaCollection->toArray())
                && $probaCollection->toArray()[1] === 1
            ))
            ->andReturn(0)
            ->once()
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
        ;

        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $consumableDisease = $this->consumableDiseaseService->createConsumableDiseases('name', $daedalus);

        $this->assertInstanceOf(ConsumableDisease::class, $consumableDisease);
        $this->assertCount(1, $consumableDisease->getDiseases());
        /** @var ConsumableDiseaseAttribute $disease */
        $disease = $consumableDisease->getDiseases()->first();
        $this->assertEquals('Disease 1', $disease->getDisease());
        $this->assertEquals(100, $disease->getRate());
        $this->assertEquals(0, $disease->getDelayMin());
        $this->assertEquals(0, $disease->getDelayLength());
    }

    public function testCreateConsumableCure()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $consumableDiseaseConfig = new ConsumableDiseaseConfig();
        $consumableDiseaseConfig
            ->setEffectNumber([1 => 1])
            ->setCuresName(['Disease 1' => 5])
            ->setDiseasesName([])
            ->setCuresChances([30 => 1])
            ->setDiseasesChances([])
            ->setDiseasesDelayMin([1 => 1])
            ->setDiseasesDelayLength([5 => 1, 8 => 5])
            ->setCauseName('name')
        ;

        $gameConfig->addConsumableDiseaseConfig($consumableDiseaseConfig);

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(1, $probaCollection->toArray())
                && $probaCollection->toArray()[1] === 1
            ))
            ->andReturn(1)
            ->times(1)
        ;

        // One cure and one disease
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->with(range(1, 1), 1)
            ->andReturn([1])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaCollection')
            ->withArgs(fn ($probaCollection, $number) => (
                $probaCollection instanceof ProbaCollection
                && key_exists('Disease 1', $probaCollection->toArray())
                && $probaCollection->toArray()['Disease 1'] === 5
                && $number === 1
            ))
            ->andReturn(['Disease 1'])
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->withArgs(fn ($probaCollection) => (
                $probaCollection instanceof ProbaCollection
                && key_exists(30, $probaCollection->toArray())
                && $probaCollection->toArray()[30] === 1
            ))
            ->andReturn(30)
            ->once()
        ;

        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
        ;

        $this->entityManager
            ->shouldReceive('flush')
            ->once()
        ;

        $consumableDisease = $this->consumableDiseaseService->createConsumableDiseases('name', $daedalus);

        $this->assertInstanceOf(ConsumableDisease::class, $consumableDisease);
        $this->assertCount(1, $consumableDisease->getCures());
        /** @var ConsumableDiseaseAttribute $cure */
        $cure = $consumableDisease->getCures()->first();
        $this->assertEquals('Disease 1', $cure->getDisease());
        $this->assertEquals(TypeEnum::CURE, $cure->getType());
        $this->assertEquals(0, $cure->getDelayMin());
        $this->assertEquals(0, $cure->getDelayLength());
    }
}
