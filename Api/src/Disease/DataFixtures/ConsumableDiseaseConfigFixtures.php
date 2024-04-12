<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Config\ConsumableDiseaseConfig;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Equipment\Enum\GameDrugEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;

class ConsumableDiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        /** @var DiseaseCauseConfig $diseaseCausesConfig */
        $diseaseCausesConfig = $this->getReference(DiseaseCausesConfigFixtures::ALIEN_FRUIT_DISEASE_CAUSE_CONFIG);

        $diseases = $diseaseCausesConfig->getDiseases();

        foreach (GameFruitEnum::getAlienFruits() as $fruitName) {
            $alienFruitDiseasesConfig = new ConsumableDiseaseConfig();
            $alienFruitDiseasesConfig
                ->setCauseName($fruitName)
                ->setDiseasesName($diseases->toArray())
                ->setCuresName($diseases->toArray())
                ->setDiseasesChances([100 => 64, 25 => 1, 30 => 2, 35 => 3, 40 => 4, 45 => 5,
                    50 => 6, 55 => 5, 60 => 4, 65 => 3, 70 => 2, 75 => 1, ])
                ->setCuresChances([100 => 64, 25 => 1, 30 => 2, 35 => 3, 40 => 4, 45 => 5,
                    50 => 6, 55 => 5, 60 => 4, 65 => 3, 70 => 2, 75 => 1, ])
                ->setDiseasesDelayMin([0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,
                    6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1, 11 => 1, ])
                ->setDiseasesDelayLength([0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1, 8 => 1])
                ->setEffectNumber([0 => 35, 1 => 40, 2 => 15, 3 => 9, 4 => 1])
                ->appendConfigKeyToName(GameConfigEnum::DEFAULT);
            $manager->persist($alienFruitDiseasesConfig);
            $gameConfig->addConsumableDiseaseConfig($alienFruitDiseasesConfig);
        }

        $junkbumpkinitis = new ConsumableDiseaseAttribute();
        $junkbumpkinitis
            ->setDisease(DiseaseEnum::JUNKBUMPKINITIS);

        $jumpkinDiseasesConfig = new ConsumableDiseaseConfig();
        $jumpkinDiseasesConfig
            ->setCauseName(GameFruitEnum::JUMPKIN)
            ->setAttributes(new ArrayCollection([$junkbumpkinitis]))
            ->appendConfigKeyToName(GameConfigEnum::DEFAULT);
        $junkbumpkinitis->setConsumableDiseaseConfig($jumpkinDiseasesConfig);

        $manager->persist($jumpkinDiseasesConfig);
        $gameConfig->addConsumableDiseaseConfig($jumpkinDiseasesConfig);

        $acidReflux = new ConsumableDiseaseAttribute();
        $acidReflux
            ->setDisease(DiseaseEnum::ACID_REFLUX)
            ->setRate(50)
            ->setDelayMin(4)
            ->setDelayLength(4);

        $tapeworm = new ConsumableDiseaseAttribute();
        $tapeworm
            ->setDisease(DiseaseEnum::TAPEWORM)
            ->setRate(25)
            ->setDelayMin(4)
            ->setDelayLength(4);

        $alienSteak = new ConsumableDiseaseConfig();
        $alienSteak
            ->setCauseName(GameRationEnum::ALIEN_STEAK)
            ->setAttributes([$acidReflux, $tapeworm])
            ->appendConfigKeyToName(GameConfigEnum::DEFAULT);
        $acidReflux->setConsumableDiseaseConfig($alienSteak);
        $tapeworm->setConsumableDiseaseConfig($alienSteak);
        $manager->persist($alienSteak);
        $gameConfig->addConsumableDiseaseConfig($alienSteak);

        $nausea = new ConsumableDiseaseAttribute();
        $nausea
            ->setDisease(DiseaseEnum::SLIGHT_NAUSEA)
            ->setRate(55);
        $manager->persist($nausea);

        $vitaminBar = new ConsumableDiseaseConfig();
        $vitaminBar
            ->setCauseName(GameRationEnum::SUPERVITAMIN_BAR)
            ->setAttributes(new ArrayCollection([$nausea]))
            ->appendConfigKeyToName(GameConfigEnum::DEFAULT);

        $nausea->setConsumableDiseaseConfig($vitaminBar);
        $manager->persist($vitaminBar);
        $gameConfig->addConsumableDiseaseConfig($vitaminBar);

        $cures = [
            DiseaseEnum::VITAMIN_DEFICIENCY => 1,
            DiseaseEnum::SYPHILIS => 1,
            DiseaseEnum::SKIN_INFLAMMATION => 1,
            DiseaseEnum::GASTROENTERIS => 1,
            DiseaseEnum::FLU => 1,
            DiseaseEnum::SEPSIS => 1,
            DiseaseEnum::COLD => 1,
            DiseaseEnum::RUBELLA => 1,
            DiseaseEnum::SINUS_STORM => 1,
            DiseaseEnum::TAPEWORM => 1,
            DisorderEnum::PARANOIA => 1,
            DisorderEnum::DEPRESSION => 1,
            DisorderEnum::CHRONIC_MIGRAINE => 1,
        ];
        foreach (GameDrugEnum::getAll() as $drugName) {
            $drugDiseaseConfig = new ConsumableDiseaseConfig();
            $drugDiseaseConfig
                ->setCauseName($drugName)
                ->setCuresName($cures)
                ->setCuresChances([100 => 1])
                ->setEffectNumber([1 => 60, 2 => 30, 3 => 8, 4 => 1])
                ->appendConfigKeyToName(GameConfigEnum::DEFAULT);
            $manager->persist($drugDiseaseConfig);
            $gameConfig->addConsumableDiseaseConfig($drugDiseaseConfig);
        }

        // special drug for tests
        $drugDiseaseConfig = new ConsumableDiseaseConfig();
        $drugDiseaseConfig
            ->setCauseName('prozac_test')
            ->setCuresName([DisorderEnum::DEPRESSION => 1])
            ->setCuresChances([100 => 1])
            ->setEffectNumber([1 => 1])
            ->appendConfigKeyToName(GameConfigEnum::TEST);
        $manager->persist($drugDiseaseConfig);
        $gameConfig->addConsumableDiseaseConfig($drugDiseaseConfig);

        $fluHealerDiseaseConfig = new ConsumableDiseaseConfig();
        $fluHealerDiseaseConfig
            ->setCauseName('flu_healer_test')
            ->setCuresName([DiseaseEnum::FLU => 1])
            ->setCuresChances([100 => 1])
            ->setEffectNumber([1 => 1])
            ->appendConfigKeyToName(GameConfigEnum::TEST);
        $manager->persist($fluHealerDiseaseConfig);
        $gameConfig->addConsumableDiseaseConfig($fluHealerDiseaseConfig);

        $manager->persist($gameConfig);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
            DiseaseCausesConfigFixtures::class,
        ];
    }
}
