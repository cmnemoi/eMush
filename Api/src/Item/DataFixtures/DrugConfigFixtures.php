<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Enum\GameDrugEnum;


class DrugConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);


        $drugType = new Drug();
        $drugType
            ->setMoralPoints([-2, 0, 1, 2, 3])
            ->setActionPoints([0, 1, 2, 3])
            ->setMovementPoints([0, 2, 4])
            ->setCures([
                DiseaseEnum::VITAMIN_DEFICIENCY,
                DiseaseEnum::SYPHILIS,
                DiseaseEnum::SKIN_INFLAMMATION,
                DiseaseEnum::GASTROENTERIS,
                DiseaseEnum::FLU,
                DiseaseEnum::SEPTIS,
                DiseaseEnum::COLD,
                DiseaseEnum::RUBELLA,
                DiseaseEnum::SINUS_STORM,
                DiseaseEnum::TAPEWORM,
                DisorderEnum::PARANOIA,
                DisorderEnum::DEPRESSION,
                DisorderEnum::CHRONIC_MIGRAINE])
            ->setCuresNumber([1,2,3,4])
        ;
        foreach(GameDrugEnum->getAll() as $drugName){


	        $drug = new Item();
	        $drug
	            ->setGameConfig($gameConfig)
	            ->setName($drugName)
	            ->setIsHeavy(false)
	            ->setIsTakeable(true)
	            ->setIsDropable(true)
	            ->setIsStackable(true)
	            ->setIsHideable(true)
	            ->setIsFireDestroyable(true)
	            ->setIsFireBreakable(false)
	            ->setTypes(new ArrayCollection([$drugType]))
	        ;
	        $manager->persist($drug);
	     }
	     $manager->persist($drugType);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
