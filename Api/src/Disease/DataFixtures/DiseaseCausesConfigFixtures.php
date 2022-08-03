<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DiseaseCausesConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public const DEFAULT_DISEASE_CONFIG = 'default.disease.config';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $diseaseCauses = new DiseaseCauseConfig();

        $diseaseCauses->setGameConfig($gameConfig);

        $diseaseCauses->setAlienFruitDiseases(
            [
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
            ]
        );

        $diseaseCauses->setPerishedFoodDiseases([DiseaseEnum::FOOD_POISONING]);

        $manager->persist($diseaseCauses);

        $manager->flush();

        $this->addReference(self::DEFAULT_DISEASE_CONFIG, $diseaseCauses);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
