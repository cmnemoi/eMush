<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\ConsumableDiseaseCharacteristic;
use Mush\Disease\Entity\ConsumableDiseaseConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class ConsumableDiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (GameFruitEnum::getAlienFruits() as $fruitName) {
            $alienFruitDiseasesConfig = new ConsumableDiseaseConfig();
            $alienFruitDiseasesConfig
                ->setName($fruitName)
                ->setGameConfig($gameConfig)
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
            ;
            $manager->persist($alienFruitDiseasesConfig);
        }

        $junkbumpkinitis = new ConsumableDiseaseCharacteristic();
        $junkbumpkinitis
            ->setDisease(DiseaseEnum::JUNKBUMPKINITIS)
        ;

        $junkinDiseasesConfig = new ConsumableDiseaseConfig();
        $junkinDiseasesConfig
            ->setName(GameFruitEnum::JUNKIN)
            ->setGameConfig($gameConfig)
            ->setDiseases(new ArrayCollection([$junkbumpkinitis]))
        ;
        $junkbumpkinitis->setConsumableDiseaseConfig($junkinDiseasesConfig);

        $manager->persist($junkinDiseasesConfig);

        $acidReflux = new ConsumableDiseaseCharacteristic();
        $acidReflux
            ->setDisease(DiseaseEnum::ACID_REFLUX)
            ->setRate(50)
            ->setDelayMin(4)
            ->setDelayLength(4)
        ;

        $tapeworm = new ConsumableDiseaseCharacteristic();
        $tapeworm
            ->setDisease(DiseaseEnum::TAPEWORM)
            ->setRate(25)
            ->setDelayMin(4)
            ->setDelayLength(4)
        ;

        $alienSteak = new ConsumableDiseaseConfig();
        $alienSteak
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::ALIEN_STEAK)
            ->setDiseases(new ArrayCollection([$acidReflux, $tapeworm]))
        ;
        $acidReflux->setConsumableDiseaseConfig($alienSteak);
        $tapeworm->setConsumableDiseaseConfig($alienSteak);
        $manager->persist($alienSteak);

        $nausea = new ConsumableDiseaseCharacteristic();
        $nausea
            ->setDisease(DiseaseEnum::SLIGHT_NAUSEA)
            ->setRate(55)
        ;
        $manager->persist($nausea);

        $vitaminBar = new ConsumableDiseaseConfig();
        $vitaminBar
            ->setGameConfig($gameConfig)
            ->setName(GameRationEnum::SUPERVITAMIN_BAR)
            ->setDiseases(new ArrayCollection([$nausea]))
        ;

        $nausea->setConsumableDiseaseConfig($vitaminBar);
        $manager->persist($vitaminBar);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
