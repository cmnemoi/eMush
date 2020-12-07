<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Enum\ExtraEffectEnum;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;

class FruitPlantConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $bananaMechanic = new Fruit();
        $bananaMechanic
            ->setPlantName(GamePlantEnum::BANANA_TREE)
            ->setActionPoints([1])
            ->setMovementPoints([0])
            ->setHealthPoints([1])
            ->setMoralPoints([1])

        ;

        $banana = new ItemConfig();
        $banana
            ->setGameConfig($gameConfig)
            ->setName(GameFruitEnum::BANANA)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bananaMechanic]))
        ;
        $manager->persist($bananaMechanic);
        $manager->persist($banana);

        $bananaTreeMechanic = new Plant();
        //  possibilities are stored as key, array value represent the probability to get the key value
        $bananaTreeMechanic
            ->setFruit($banana)
            ->setMaturationTime([36 => 1])
            ->setMaxOxygen(1)
            ->setMinOxygen(1)
        ;

        $bananaTree = new ItemConfig();
        $bananaTree
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BANANA_TREE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bananaTreeMechanic]))
        ;
        $manager->persist($bananaTreeMechanic);
        $manager->persist($bananaTree);

        $alienFruitPlant = [
            GameFruitEnum::CREEPNUT => GamePlantEnum::CREEPIST,
            GameFruitEnum::MEZTINE => GamePlantEnum::CACTAX,
            GameFruitEnum::GUNTIFLOP => GamePlantEnum::BIFFLON,
            GameFruitEnum::PLOSHMINA => GamePlantEnum::PULMMINAGRO,
            GameFruitEnum::PRECATI => GamePlantEnum::PRECATUS,
            GameFruitEnum::BOTTINE => GamePlantEnum::BUTTALIEN,
            GameFruitEnum::FRAGILANE => GamePlantEnum::PLATACIA,
            GameFruitEnum::ANEMOLE => GamePlantEnum::TUBILISCUS,
            GameFruitEnum::PENICRAFT => GamePlantEnum::GRAAPSHOOT,
            GameFruitEnum::KUBINUS => GamePlantEnum::FIBONICCUS,
            GameFruitEnum::CALEBOOT => GamePlantEnum::MYCOPIA,
            GameFruitEnum::FILANDRA => GamePlantEnum::ASPERAGUNK,
            ];

        foreach ($alienFruitPlant as $fruitName => $plantName) {
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
                            DiseaseEnum::TAPEWORM => 8, ])
                 ->setDiseasesEffectChance([100 => 64, 25 => 1, 30 => 2, 35 => 3, 40 => 4, 45 => 5,
                                                       50 => 6, 55 => 5, 60 => 4, 65 => 3, 70 => 2, 65 => 1, ])
                 ->setDiseasesEffectDelayMin([0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,
                                                      6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, ])
                 ->setDiseasesEffectDelayLength([0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1])
                 ->setFruitEffectsNumber([0 => 35, 1 => 40, 2 => 15, 3 => 9, 4 => 1])
                 ->setExtraEffects([ExtraEffectEnum::EXTRA_PA_GAIN => 50])
            ;
            $manager->persist($alienFruitMechanic);

            $alienFruit = new ItemConfig();
            $alienFruit
            ->setGameConfig($gameConfig)
            ->setName($fruitName)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$alienFruitMechanic]))
            ;
            $manager->persist($alienFruit);

            $alienPlantMechanic = new Plant();
            $alienPlantMechanic
                ->setFruit($alienFruit)
                ->setMaturationTime([2 => 7, 4 => 7, 8 => 24, 12 => 14, 16 => 7, 24 => 7, 48 => 7])
                ->setMaxOxygen(1)
                ->setMinOxygen(1)
            ;

            $alienPlant = new ItemConfig();
            $alienPlant
            ->setGameConfig($gameConfig)
            ->setName($plantName)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$alienPlantMechanic]))
            ;
            $manager->persist($alienPlantMechanic);
            $manager->persist($alienPlant);
        }

        $junkinMechanic = new Fruit();
        $junkinMechanic
            ->setPlantName(GamePlantEnum::BUMPJUNKIN)
            ->setActionPoints([3])
            ->setMovementPoints([0])
            ->setHealthPoints([1])
            ->setMoralPoints([1])
            ->setDiseasesChances([DiseaseEnum::JUNKBUMPKINITIS => 100])
            ->setDiseasesDelayMin([DiseaseEnum::JUNKBUMPKINITIS => 0])
            ->setDiseasesDelayLength([DiseaseEnum::JUNKBUMPKINITIS => 0])
        ;

        $junkin = new ItemConfig();
        $junkin
            ->setGameConfig($gameConfig)
            ->setName(GameFruitEnum::JUNKIN)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$junkinMechanic]))
        ;
        $manager->persist($junkinMechanic);
        $manager->persist($junkin);

        $bumpjunkinMechanic = new Plant();
        $bumpjunkinMechanic
            ->setFruit($junkin)
            ->setMaturationTime([8 => 1])
            ->setMaxOxygen(1)
            ->setMinOxygen(1)
        ;

        $bumpjunkin = new ItemConfig();
        $bumpjunkin
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BUMPJUNKIN)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$bumpjunkinMechanic]))
        ;
        $manager->persist($bumpjunkinMechanic);
        $manager->persist($bumpjunkin);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
