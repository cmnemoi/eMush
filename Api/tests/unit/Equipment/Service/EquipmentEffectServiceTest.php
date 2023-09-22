<?php

namespace Mush\Tests\unit\Equipment\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Repository\ConsumableEffectRepository;
use Mush\Equipment\Repository\PlantEffectRepository;
use Mush\Equipment\Service\EquipmentEffectService;
use Mush\Game\Service\RandomServiceInterface;
use PHPUnit\Framework\TestCase;

class EquipmentEffectServiceTest extends TestCase
{
    /** @var ConsumableEffectRepository|Mockery\Mock */
    private ConsumableEffectRepository $consumableEffectRepository;
    /** @var PlantEffectRepository|Mockery\Mock */
    private PlantEffectRepository $plantEffectRepository;
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    private EquipmentEffectService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->consumableEffectRepository = \Mockery::mock(ConsumableEffectRepository::class);
        $this->plantEffectRepository = \Mockery::mock(PlantEffectRepository::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->service = new EquipmentEffectService(
            $this->consumableEffectRepository,
            $this->plantEffectRepository,
            $this->randomService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testGetConsumableEffect()
    {
        $daedalus = new Daedalus();
        $ration = new Ration();

        $ration
            ->setHealthPoints([0 => 1, 1 => 1, 2 => 1])
            ->setMoralPoints([0 => 5, 1 => 1, 2 => 1])
            ->setActionPoints([0 => 1, 1 => 0])
            ->setMovementPoints([1 => 1])
            ->setMovementPoints([1 => 1])
            ->setExtraEffects(['break_door' => 55])
        ;
        $consumableEffectFromRepository = new ConsumableEffect();
        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn($consumableEffectFromRepository)
            ->once()
        ;

        $consumableEffect = $this->service->getConsumableEffect($ration, $daedalus);

        $this->assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        $this->assertEquals($consumableEffectFromRepository, $consumableEffect);

        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once()
        ;
        $this->consumableEffectRepository
            ->shouldReceive('persist')
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->andReturn(2)
            ->times(4)
        ;
        $consumableEffect = $this->service->getConsumableEffect($ration, $daedalus);

        $this->assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        $this->assertEquals($daedalus, $consumableEffect->getDaedalus());
        $this->assertEquals($ration, $consumableEffect->getRation());
        $this->assertEquals(2, $consumableEffect->getActionPoint());
        $this->assertEquals(2, $consumableEffect->getMovementPoint());
        $this->assertEquals(2, $consumableEffect->getHealthPoint());
        $this->assertEquals(2, $consumableEffect->getMoralPoint());
    }

    public function testGetPlantEffect()
    {
        $daedalus = new Daedalus();
        $plant = new Plant();

        $plant
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1])
        ;
        $plantEffectFromRepository = new PlantEffect();
        $this->plantEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn($plantEffectFromRepository)
            ->once()
        ;

        $plantEffect = $this->service->getPlantEffect($plant, $daedalus);

        $this->assertInstanceOf(PlantEffect::class, $plantEffect);
        $this->assertEquals($plantEffectFromRepository, $plantEffect);

        $this->plantEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once()
        ;
        $this->plantEffectRepository
            ->shouldReceive('persist')
            ->once()
        ;

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->andReturn(8)
            ->once()
        ;
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->andReturn(1)
            ->once()
        ;
        $plantEffect = $this->service->getPlantEffect($plant, $daedalus);

        $this->assertInstanceOf(PlantEffect::class, $plantEffect);
        $this->assertEquals($daedalus, $plantEffect->getDaedalus());
        $this->assertEquals($plant, $plantEffect->getPlant());
        $this->assertEquals(1, $plantEffect->getOxygen());
        $this->assertEquals(8, $plantEffect->getMaturationTime());
    }
}
