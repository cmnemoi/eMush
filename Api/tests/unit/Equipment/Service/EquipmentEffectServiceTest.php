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

/**
 * @internal
 */
final class EquipmentEffectServiceTest extends TestCase
{
    /** @var ConsumableEffectRepository|Mockery\Mock */
    private ConsumableEffectRepository $consumableEffectRepository;

    /** @var Mockery\Mock|PlantEffectRepository */
    private PlantEffectRepository $plantEffectRepository;

    /** @var Mockery\Mock|RandomServiceInterface */
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
            ->setExtraEffects(['break_door' => 55]);
        $consumableEffectFromRepository = new ConsumableEffect();
        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn($consumableEffectFromRepository)
            ->once();

        $consumableEffect = $this->service->getConsumableEffect($ration, $daedalus);

        self::assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        self::assertSame($consumableEffectFromRepository, $consumableEffect);

        $this->consumableEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once();
        $this->consumableEffectRepository
            ->shouldReceive('persist')
            ->once();

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->andReturn(2)
            ->times(4);
        $consumableEffect = $this->service->getConsumableEffect($ration, $daedalus);

        self::assertInstanceOf(ConsumableEffect::class, $consumableEffect);
        self::assertSame($daedalus, $consumableEffect->getDaedalus());
        self::assertSame($ration, $consumableEffect->getRation());
        self::assertSame(2, $consumableEffect->getActionPoint());
        self::assertSame(2, $consumableEffect->getMovementPoint());
        self::assertSame(2, $consumableEffect->getHealthPoint());
        self::assertSame(2, $consumableEffect->getMoralPoint());
    }

    public function testGetPlantEffect()
    {
        $daedalus = new Daedalus();
        $plant = new Plant();

        $plant
            ->setOxygen([1 => 1])
            ->setMaturationTime([10 => 1]);
        $plantEffectFromRepository = new PlantEffect();
        $this->plantEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn($plantEffectFromRepository)
            ->once();

        $plantEffect = $this->service->getPlantEffect($plant, $daedalus);

        self::assertInstanceOf(PlantEffect::class, $plantEffect);
        self::assertSame($plantEffectFromRepository, $plantEffect);

        $this->plantEffectRepository
            ->shouldReceive('findOneBy')
            ->andReturn(null)
            ->once();
        $this->plantEffectRepository
            ->shouldReceive('persist')
            ->once();

        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->andReturn(8)
            ->once();
        $this->randomService
            ->shouldReceive('getSingleRandomElementFromProbaCollection')
            ->andReturn(1)
            ->once();
        $plantEffect = $this->service->getPlantEffect($plant, $daedalus);

        self::assertInstanceOf(PlantEffect::class, $plantEffect);
        self::assertSame($daedalus, $plantEffect->getDaedalus());
        self::assertSame($plant, $plantEffect->getPlant());
        self::assertSame(1, $plantEffect->getOxygen());
        self::assertSame(8, $plantEffect->getMaturationTime());
    }
}
