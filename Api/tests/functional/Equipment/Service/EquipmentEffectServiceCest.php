<?php

namespace Mush\Tests\functional\Equipment\Service;

use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Service\EquipmentEffectService;
use Mush\Tests\FunctionalTester;

class EquipmentEffectServiceCest
{
    private EquipmentEffectService $equipmentEffectService;

    public function _before(FunctionalTester $I)
    {
        $this->equipmentEffectService = $I->grabService(EquipmentEffectService::class);
    }

    public function testCreateBananaEffect(FunctionalTester $I)
    {
        $banana = $this->createBananaConfig();
        $I->haveInRepository($banana);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $bananaEffect = $this->equipmentEffectService->getConsumableEffect($banana, $daedalus);

        $I->assertEquals(1, $bananaEffect->getActionPoint());
        $I->assertEquals(0, $bananaEffect->getMovementPoint());
        $I->assertEquals(1, $bananaEffect->getHealthPoint());
        $I->assertEquals(1, $bananaEffect->getMoralPoint());
    }

    public function testCreateRandomEffect(FunctionalTester $I)
    {
        $rationEffect = new Ration();
        $rationEffect
            ->setActionPoints([1 => 1, 2 => 2, 3 => 3])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([1 => 1, 3 => 1])
            ->setMoralPoints([1000 => 1000])
            ->setName('ration_test');
        $I->haveInRepository($rationEffect);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $consumableEffect = $this->equipmentEffectService->getConsumableEffect($rationEffect, $daedalus);

        $I->assertTrue(\in_array($consumableEffect->getActionPoint(), [1, 2, 3], true));
        $I->assertEquals(0, $consumableEffect->getMovementPoint());
        $I->assertTrue(\in_array($consumableEffect->getHealthPoint(), [1, 3], true));
        $I->assertEquals(1000, $consumableEffect->getMoralPoint());

        $existingEffect = $this->equipmentEffectService->getConsumableEffect($rationEffect, $daedalus);

        $I->assertEquals($consumableEffect, $existingEffect);
    }

    public function testCreateAlienFruitEffect(FunctionalTester $I)
    {
        $alienFruit = $this->createAlienFruitConfig('plant');
        $I->haveInRepository($alienFruit);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $alienFruitEffect = $this->equipmentEffectService->getConsumableEffect($alienFruit, $daedalus);

        $existingEffect = $this->equipmentEffectService->getConsumableEffect($alienFruit, $daedalus);

        $I->assertEquals($alienFruitEffect, $existingEffect);
    }

    private function createBananaConfig(): Fruit
    {
        $bananaMechanic = new Fruit();
        $bananaMechanic
            ->setPlantName(GamePlantEnum::BANANA_TREE)
            ->setActionPoints([1 => 1])
            ->setMovementPoints([0 => 1])
            ->setHealthPoints([1 => 1])
            ->setMoralPoints([1 => 1])
            ->setName('fruit_banana_test');

        return $bananaMechanic;
    }

    private function createAlienFruitConfig($plantName): Fruit
    {
        $alienFruitMechanic = new Fruit();
        $alienFruitMechanic
            ->setPlantName($plantName)
            ->setActionPoints([1 => 90, 2 => 9, 3 => 1])
            ->setMoralPoints([0 => 30, 1 => 70])
            ->setExtraEffects([ExtraEffectEnum::EXTRA_PA_GAIN => 50])
            ->setName('fruit_alien_test');

        return $alienFruitMechanic;
    }
}
