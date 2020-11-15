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
                DiseaseEnum::VITAMIN_DEFICIENCY => 100,
                DiseaseEnum::SYPHILIS => 100,
                DiseaseEnum::SKIN_INFLAMMATION => 100,
                DiseaseEnum::GASTROENTERIS => 100,
                DiseaseEnum::FLU => 100,
                DiseaseEnum::SEPTIS => 100,
                DiseaseEnum::COLD => 100,
                DiseaseEnum::RUBELLA => 100,
                DiseaseEnum::SINUS_STORM => 100,
                DiseaseEnum::TAPEWORM => 100,
                DisorderEnum::PARANOIA => 100,
                DisorderEnum::DEPRESSION => 100,
                DisorderEnum::CHRONIC_MIGRAINE => 100])
            ->setEffectsNumber([1,2,3,4])
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
