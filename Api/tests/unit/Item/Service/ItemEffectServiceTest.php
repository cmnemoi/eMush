<?php

namespace Mush\Test\Item\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\ConsumableEffect;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\PlantEffect;
use Mush\Item\Repository\ConsumableEffectRepository;
use Mush\Item\Repository\PlantEffectRepository;
use Mush\Item\Service\ItemEffectService;
use PHPUnit\Framework\TestCase;

class ItemEffectServiceTest extends TestCase
{
    /** @var ConsumableEffectRepository | Mockery\Mock */
    private ConsumableEffectRepository $consumableEffectRepository;
    /** @var PlantEffectRepository | Mockery\Mock */
    private PlantEffectRepository $plantEffectRepository;
    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    private ItemEffectService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->consumableEffectRepository = Mockery::mock(ConsumableEffectRepository::class);
        $this->plantEffectRepository = Mockery::mock(PlantEffectRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new ItemEffectService(
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
        Mockery::close();
    }

    public function testGetConsumableEffect()
    {
        $daedalus = new Daedalus();
        $ration = new Ration();

        $ration
            ->setHealthPoints([0,1,2])
            ->setMoralPoints([0,1,2])
            ->setActionPoints([0,1,2])
            ->setMovementPoints([0,1,2])
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
            ->shouldReceive('getRandomElements')
            ->andReturn([2])
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
            ->setMinOxygen(0)
            ->setMaxOxygen(10)
            ->setMinMaturationTime(0)
            ->setMaxMaturationTime(10)
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
            ->shouldReceive('random')
            ->andReturn(8)
        ;
        $plantEffect = $this->service->getPlantEffect($plant, $daedalus);

        $this->assertInstanceOf(PlantEffect::class, $plantEffect);
        $this->assertEquals($daedalus, $plantEffect->getDaedalus());
        $this->assertEquals($plant, $plantEffect->getPlant());
        $this->assertEquals(8, $plantEffect->getOxygen());
        $this->assertEquals(8, $plantEffect->getMaturationTime());
    }
}
