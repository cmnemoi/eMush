<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;

class DiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $foodPoisoning = new DiseaseConfig();
        $foodPoisoning->setGameConfig($gameConfig);
        $foodPoisoning->setName(DiseaseEnum::FOOD_POISONING);
        $foodPoisoning->setCauses([
            DiseaseCauseEnum::PERISHED_FOOD,
        ]);

        $manager->persist($foodPoisoning);

        $acidReflux = new DiseaseConfig();
        $acidReflux->setGameConfig($gameConfig);
        $acidReflux->setName(DiseaseEnum::ACID_REFLUX);
        $acidReflux->setCauses([]);

        $manager->persist($foodPoisoning);

        $blackBite = new DiseaseConfig();
        $blackBite->setGameConfig($gameConfig);
        $blackBite->setName(DiseaseEnum::BLACK_BITE);
        $blackBite->setCauses([]);

        $manager->persist($blackBite);

        $catAllergy = new DiseaseConfig();
        $catAllergy->setGameConfig($gameConfig);
        $catAllergy->setName(DiseaseEnum::CAT_ALLERGY);
        $catAllergy->setCauses([]);

        $manager->persist($catAllergy);

        $cold = new DiseaseConfig();
        $cold->setGameConfig($gameConfig);
        $cold->setName(DiseaseEnum::COLD);
        $cold->setCauses([]);

        $manager->persist($cold);

        $extremeTinnitus = new DiseaseConfig();
        $extremeTinnitus->setGameConfig($gameConfig);
        $extremeTinnitus->setName(DiseaseEnum::EXTREME_TINNITUS);
        $extremeTinnitus->setCauses([]);

        $manager->persist($extremeTinnitus);

        $flu = new DiseaseConfig();
        $flu->setGameConfig($gameConfig);
        $flu->setName(DiseaseEnum::FLU);
        $flu->setCauses([]);

        $manager->persist($foodPoisoning);

        $fungicInfection = new DiseaseConfig();
        $fungicInfection->setGameConfig($gameConfig);
        $fungicInfection->setName(DiseaseEnum::FUNGIC_INFECTION);
        $fungicInfection->setCauses([]);

        $manager->persist($fungicInfection);

        $gastroenteritis = new DiseaseConfig();
        $gastroenteritis->setGameConfig($gameConfig);
        $gastroenteritis->setName(DiseaseEnum::GASTROENTERIS);
        $gastroenteritis->setCauses([]);

        $manager->persist($gastroenteritis);

        $junkbumpkinitis = new DiseaseConfig();
        $junkbumpkinitis->setGameConfig($gameConfig);
        $junkbumpkinitis->setName(DiseaseEnum::JUNKBUMPKINITIS);
        $junkbumpkinitis->setCauses([]);

        $manager->persist($foodPoisoning);

        $migraine = new DiseaseConfig();
        $migraine->setGameConfig($gameConfig);
        $migraine->setName(DiseaseEnum::MIGRAINE);
        $migraine->setCauses([]);

        $manager->persist($migraine);

        $mushAllergy = new DiseaseConfig();
        $mushAllergy->setGameConfig($gameConfig);
        $mushAllergy->setName(DiseaseEnum::MUSH_ALLERGY);
        $mushAllergy->setCauses([]);

        $manager->persist($mushAllergy);

        $quincksOedema = new DiseaseConfig();
        $quincksOedema->setGameConfig($gameConfig);
        $quincksOedema->setName(DiseaseEnum::QUINCKS_OEDEMA);
        $quincksOedema->setCauses([]);

        $manager->persist($quincksOedema);

        $rejuvenation = new DiseaseConfig();
        $rejuvenation->setGameConfig($gameConfig);
        $rejuvenation->setName(DiseaseEnum::REJUVENATION);
        $rejuvenation->setCauses([]);

        $manager->persist($rejuvenation);

        $rubella = new DiseaseConfig();
        $rubella->setGameConfig($gameConfig);
        $rubella->setName(DiseaseEnum::RUBELLA);
        $rubella->setCauses([]);

        $manager->persist($rubella);

        $sepsis = new DiseaseConfig();
        $sepsis->setGameConfig($gameConfig);
        $sepsis->setName(DiseaseEnum::SEPSIS);
        $sepsis->setCauses([]);

        $manager->persist($sepsis);

        $sinusStorm = new DiseaseConfig();
        $sinusStorm->setGameConfig($gameConfig);
        $sinusStorm->setName(DiseaseEnum::SINUS_STORM);
        $sinusStorm->setCauses([]);

        $manager->persist($sinusStorm);

        $skinInflammation = new DiseaseConfig();
        $skinInflammation->setGameConfig($gameConfig);
        $skinInflammation->setName(DiseaseEnum::SKIN_INFLAMMATION);
        $skinInflammation->setCauses([]);

        $manager->persist($skinInflammation);

        $nausea = new DiseaseConfig();
        $nausea->setGameConfig($gameConfig);
        $nausea->setName(DiseaseEnum::SLIGHT_NAUSEA);
        $nausea->setCauses([]);

        $manager->persist($nausea);

        $smallPox = new DiseaseConfig();
        $smallPox->setGameConfig($gameConfig);
        $smallPox->setName(DiseaseEnum::SMALLPOX);
        $smallPox->setCauses([]);

        $manager->persist($smallPox);

        $spaceRabies = new DiseaseConfig();
        $spaceRabies->setGameConfig($gameConfig);
        $spaceRabies->setName(DiseaseEnum::SPACE_RABIES);
        $spaceRabies->setCauses([]);

        $manager->persist($spaceRabies);

        $syphilis = new DiseaseConfig();
        $syphilis->setGameConfig($gameConfig);
        $syphilis->setName(DiseaseEnum::SYPHILIS);
        $syphilis->setCauses([]);

        $manager->persist($syphilis);

        $tapeworm = new DiseaseConfig();
        $tapeworm->setGameConfig($gameConfig);
        $tapeworm->setName(DiseaseEnum::TAPEWORM);
        $tapeworm->setCauses([]);

        $manager->persist($tapeworm);

        $vitaminDeficiency = new DiseaseConfig();
        $vitaminDeficiency->setGameConfig($gameConfig);
        $vitaminDeficiency->setName(DiseaseEnum::VITAMIN_DEFICIENCY);
        $vitaminDeficiency->setCauses([]);

        $manager->persist($vitaminDeficiency);

        $manager->flush();

        $this->addReference(DiseaseEnum::FOOD_POISONING, $foodPoisoning);
        $this->addReference(DiseaseEnum::VITAMIN_DEFICIENCY, $vitaminDeficiency);
        $this->addReference(DiseaseEnum::TAPEWORM, $tapeworm);
        $this->addReference(DiseaseEnum::SYPHILIS, $syphilis);
        $this->addReference(DiseaseEnum::SPACE_RABIES, $spaceRabies);
        $this->addReference(DiseaseEnum::SMALLPOX, $smallPox);
        $this->addReference(DiseaseEnum::SLIGHT_NAUSEA, $nausea);
        $this->addReference(DiseaseEnum::SKIN_INFLAMMATION, $skinInflammation);
        $this->addReference(DiseaseEnum::SINUS_STORM, $sinusStorm);
        $this->addReference(DiseaseEnum::SEPSIS, $sepsis);
        $this->addReference(DiseaseEnum::RUBELLA, $rubella);
        $this->addReference(DiseaseEnum::REJUVENATION, $rejuvenation);
        $this->addReference(DiseaseEnum::QUINCKS_OEDEMA, $quincksOedema);
        $this->addReference(DiseaseEnum::MUSH_ALLERGY, $mushAllergy);
        $this->addReference(DiseaseEnum::MIGRAINE, $migraine);
        $this->addReference(DiseaseEnum::JUNKBUMPKINITIS, $junkbumpkinitis);
        $this->addReference(DiseaseEnum::GASTROENTERIS, $gastroenteritis);
        $this->addReference(DiseaseEnum::FUNGIC_INFECTION, $fungicInfection);
        $this->addReference(DiseaseEnum::FLU, $flu);
        $this->addReference(DiseaseEnum::EXTREME_TINNITUS, $extremeTinnitus);
        $this->addReference(DiseaseEnum::COLD, $cold);
        $this->addReference(DiseaseEnum::CAT_ALLERGY, $catAllergy);
        $this->addReference(DiseaseEnum::BLACK_BITE, $blackBite);
        $this->addReference(DiseaseEnum::ACID_REFLUX, $acidReflux);
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
