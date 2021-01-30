<?php

namespace functional\Equipment\Service;

use App\Tests\FunctionalTester;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Service\EquipmentEffectService;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;

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
        ;
        $I->haveInRepository($rationEffect);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        $consumableEffect = $this->equipmentEffectService->getConsumableEffect($rationEffect, $daedalus);

        $I->assertTrue(in_array($consumableEffect->getActionPoint(), [1, 2, 3]));
        $I->assertEquals(0, $consumableEffect->getMovementPoint());
        $I->assertTrue(in_array($consumableEffect->getHealthPoint(), [1, 3]));
        $I->assertEquals(1000, $consumableEffect->getMoralPoint());

        $existingEffect = $this->equipmentEffectService->getConsumableEffect($rationEffect, $daedalus);

        $I->assertEquals($consumableEffect, $existingEffect);
    }

    public function testCreateAlienFruitEffect(FunctionalTester $I)
    {
        for ($i = 1; $i <= 50; ++$i) {
            $alienFruit = $this->createAlienFruitConfig('plant' . strval($i));
            $I->haveInRepository($alienFruit);

            /** @var Daedalus $daedalus */
            $daedalus = $I->have(Daedalus::class);

            $alienFruitEffect = $this->equipmentEffectService->getConsumableEffect($alienFruit, $daedalus);

            $existingEffect = $this->equipmentEffectService->getConsumableEffect($alienFruit, $daedalus);

            $I->assertEquals($alienFruitEffect, $existingEffect);
        }
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
        ;

        return $bananaMechanic;
    }

    private function createAlienFruitConfig($plantName): Fruit
    {
        $alienFruitMechanic = new Fruit();
        $alienFruitMechanic
            ->setPlantName($plantName)
            ->setActionPoints([1 => 90, 2 => 9, 3 => 1])
            ->setMoralPoints([0 => 30, 1 => 70])
            ->setDiseasesName([
                DiseaseEnum::CAT_ALLERGY => 1,
                DiseaseEnum::MUSH_ALLERGY => 1,
                DiseaseEnum::SEPSIS => 1,
                DiseaseEnum::SLIGHT_NAUSEA => 1,
                DiseaseEnum::SMALLPOX => 1,
                DiseaseEnum::SYPHILIS => 1,
                DisorderEnum::AILUROPHOBIA => 1,
                DisorderEnum::COPROLALIA => 1,
                DisorderEnum::SPLEEN => 1,
                DisorderEnum::WEAPON_PHOBIA => 1,
                DisorderEnum::CHRONIC_VERTIGO => 1,
                DisorderEnum::PARANOIA => 1,
                DiseaseEnum::ACID_REFLUX => 2,
                DiseaseEnum::SKIN_INFLAMMATION => 2,
                DisorderEnum::AGORAPHOBIA => 2,
                DisorderEnum::CHRONIC_MIGRAINE => 2,
                DisorderEnum::VERTIGO => 2,
                DisorderEnum::DEPRESSION => 2,
                DisorderEnum::PSYCOTIC_EPISODE => 2,
                DisorderEnum::CRABISM => 4,
                DiseaseEnum::BLACK_BITE => 4,
                DiseaseEnum::COLD => 4,
                DiseaseEnum::EXTREME_TINNITUS => 4,
                DiseaseEnum::FOOD_POISONING => 4,
                DiseaseEnum::FUNGIC_INFECTION => 4,
                DiseaseEnum::REJUVENATION => 4,
                DiseaseEnum::RUBELLA => 4,
                DiseaseEnum::SINUS_STORM => 4,
                DiseaseEnum::SPACE_RABIES => 4,
                DiseaseEnum::VITAMIN_DEFICIENCY => 4,
                DiseaseEnum::FLU => 8,
                DiseaseEnum::GASTROENTERIS => 8,
                DiseaseEnum::MIGRAINE => 8,
                DiseaseEnum::TAPEWORM => 8,
            ])
            ->setDiseasesChances([100 => 64, 25 => 1, 30 => 2, 35 => 3, 40 => 4, 45 => 5,
                50 => 6, 55 => 5, 60 => 4, 65 => 3, 70 => 2, 75 => 1, ])
            ->setDiseasesDelayMin([0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,
                6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, ])
            ->setDiseasesDelayLength([0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1])
            ->setFruitEffectsNumber([0 => 35, 1 => 40, 2 => 15, 3 => 9, 4 => 1])
            ->setExtraEffects([ExtraEffectEnum::EXTRA_PA_GAIN => 50])
        ;

        return $alienFruitMechanic;
    }
}
