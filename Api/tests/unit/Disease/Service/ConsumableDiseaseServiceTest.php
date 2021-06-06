<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\ConsumableDiseaseConfig;
use Mush\Disease\Repository\ConsumableDiseaseConfigRepository;
use Mush\Disease\Repository\ConsumableDiseaseRepository;
use Mush\Disease\Service\ConsumableDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use PHPUnit\Framework\TestCase;

class ConsumableDiseaseServiceTest extends TestCase
{
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var ConsumableDiseaseRepository | Mockery\Mock */
    private ConsumableDiseaseRepository $consumableDiseaseRepository;

    /** @var ConsumableDiseaseConfigRepository | Mockery\Mock */
    private ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository;

    private ConsumableDiseaseService $consumableDiseaseService;

    /**
     * @before
     */
    public function before()
    {
        $this->consumableDiseaseRepository = Mockery::mock(ConsumableDiseaseRepository::class);
        $this->consumableDiseaseConfigRepository = Mockery::mock(ConsumableDiseaseConfigRepository::class);
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->consumableDiseaseService = new ConsumableDiseaseService(
            $this->consumableDiseaseRepository,
            $this->consumableDiseaseConfigRepository,
            $this->entityManager,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateConsumableDiseasesWithPredefinedDiseases()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $disease1 = new ConsumableDiseaseAttribute();
        $disease1->setDisease('Disease 1');
        $disease2 = new ConsumableDiseaseAttribute();
        $disease2->setDisease('Disease 2');

        $diseaseConfig = new ConsumableDiseaseConfig();
        $diseaseConfig
            ->setAttributes(new ArrayCollection([$disease1, $disease2]))
        ;

        $this->consumableDiseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
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
        $this->assertCount(2, $consumableDisease->getDiseases());
    }

    public function testCreateConsumableDiseasesWithMultipleDiseases()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $diseaseConfig = new ConsumableDiseaseConfig();
        $diseaseConfig->setEffectNumber([1 => 1]);

        $this->consumableDiseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(1)
            ->times(7)
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([1, 2])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaArray')
            ->andReturn(['diseaseName', 'otherDisease'])
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
        $this->assertCount(2, $consumableDisease->getDiseases());
    }

    public function testCreateConsumableDiseasesWithDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $diseaseConfig = new ConsumableDiseaseConfig();
        $diseaseConfig->setEffectNumber([1 => 1]);

        $this->consumableDiseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(1)
            ->times(4)
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([1])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaArray')
            ->andReturn(['diseaseName'])
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
        $this->assertEquals('diseaseName', $disease->getDisease());
        $this->assertEquals(1, $disease->getDelayMin());
        $this->assertEquals(1, $disease->getDelayLength());
    }

    public function testCreateConsumableDiseasesWithoutDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $diseaseConfig = new ConsumableDiseaseConfig();
        $diseaseConfig->setEffectNumber([1 => 1]);

        $this->consumableDiseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaArray')
            ->andReturn(1, 100, 0)
            ->times(3)
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([1])
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getRandomElementsFromProbaArray')
            ->andReturn(['diseaseName'])
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
        $this->assertEquals('diseaseName', $disease->getDisease());
        $this->assertEquals(100, $disease->getRate());
        $this->assertEquals(0, $disease->getDelayMin());
        $this->assertEquals(0, $disease->getDelayLength());
    }
}
